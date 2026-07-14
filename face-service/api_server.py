"""HTTP API phục vụ xác thực khuôn mặt cho trang web chấm công.

Chạy service này song song với Laravel:
    python face-service/api_server.py

Endpoint:
    POST /verify  {employee_id, image_base64} -> {verified, score, message}
"""

from __future__ import annotations

import base64
import sys
import threading
import time

import cv2
import numpy as np
from flask import Flask, jsonify, request

from config import configure_console, load_config
from face_engine import FaceEngine, l2_normalize
from laravel_client import LaravelClient

app = Flask(__name__)

_engine: FaceEngine | None = None
_client: LaravelClient | None = None
_config = None
_descriptors_by_employee: dict[int, list[np.ndarray]] = {}
_last_sync = 0.0
_infer_lock = threading.Lock()
_MAX_FRAME_WIDTH = 480


def _resize_frame(frame: np.ndarray, max_width: int = _MAX_FRAME_WIDTH) -> np.ndarray:
    height, width = frame.shape[:2]
    if width <= max_width:
        return frame

    scale = max_width / width
    new_size = (max_width, max(1, int(height * scale)))
    return cv2.resize(frame, new_size, interpolation=cv2.INTER_AREA)


def _init() -> None:
    global _engine, _client, _config
    configure_console()
    _config = load_config()
    if not _config.kiosk_token:
        raise RuntimeError("Thiếu FACE_KIOSK_TOKEN trong face-service/.env")

    _client = LaravelClient(_config.laravel_base_url, _config.kiosk_token, _config.request_timeout)
    print("Đang khởi tạo InsightFace...")
    _engine = FaceEngine(
        model_name=_config.model_name,
        det_size=_config.det_size_tuple,
        providers=_config.providers,
        min_det_score=_config.min_det_score,
    )
    _sync_descriptors(force=True)


def _sync_descriptors(force: bool = False) -> None:
    global _descriptors_by_employee, _last_sync
    assert _client is not None and _config is not None

    now = time.time()
    if not force and (now - _last_sync) < _config.sync_interval_seconds:
        return

    gallery = _client.fetch_gallery()
    grouped: dict[int, list[np.ndarray]] = {}
    for entry in gallery:
        grouped.setdefault(entry.employee_id, []).append(entry.embedding)

    _descriptors_by_employee = grouped
    _last_sync = now
    print(f"[sync] Đã tải mẫu khuôn mặt cho {len(grouped)} nhân viên.")


def _decode_image(image_base64: str) -> np.ndarray | None:
    payload = image_base64
    if "," in payload:
        payload = payload.split(",", 1)[1]

    try:
        binary = base64.b64decode(payload, validate=True)
    except (ValueError, TypeError):
        return None

    arr = np.frombuffer(binary, dtype=np.uint8)
    frame = cv2.imdecode(arr, cv2.IMREAD_COLOR)
    return frame


@app.get("/")
def index():
    laravel_url = _config.laravel_base_url if _config else "http://127.0.0.1:8000"
    attendance_url = f"{laravel_url.rstrip('/')}/employee/attendance"
    return (
        f"""<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Face API — Không phải trang chấm công</title>
  <style>
    body {{ font-family: system-ui, sans-serif; max-width: 520px; margin: 48px auto; padding: 0 20px; color: #1e293b; }}
    h1 {{ font-size: 1.25rem; }}
    a {{ color: #2563eb; font-weight: 600; }}
    .box {{ background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 16px; margin: 16px 0; }}
    code {{ background: #f1f5f9; padding: 2px 6px; border-radius: 4px; }}
  </style>
</head>
<body>
  <h1>Đây là API nhận diện khuôn mặt (đang chạy ✓)</h1>
  <p>Địa chỉ <code>127.0.0.1:5555</code> <strong>không có camera</strong> — chỉ dùng nội bộ cho Laravel.</p>
  <div class="box">
    <p><strong>Để quét mặt chấm công, mở trang web Laravel:</strong></p>
    <p><a href="{attendance_url}">{attendance_url}</a></p>
    <p>Đăng nhập → bấm nút <strong>Quét mặt</strong> ở thanh dưới → camera sẽ hiện trên trang đó.</p>
  </div>
  <p>API: <code>GET /health</code> · <code>POST /verify</code></p>
</body>
</html>""",
        200,
        {"Content-Type": "text/html; charset=utf-8"},
    )


@app.get("/health")
def health():
    return jsonify({"ok": True, "employees": len(_descriptors_by_employee)})


@app.post("/verify")
def verify():
    assert _engine is not None and _config is not None

    if not _infer_lock.acquire(blocking=False):
        return jsonify({
            "verified": False,
            "score": 0.0,
            "message": "Máy chủ đang xử lý ảnh trước, vui lòng chờ...",
        }), 429

    try:
        data = request.get_json(silent=True) or {}
        employee_id = data.get("employee_id")
        image_base64 = data.get("image_base64")

        if not employee_id or not image_base64:
            return jsonify({"verified": False, "score": 0.0, "message": "Thiếu employee_id hoặc ảnh."}), 400

        try:
            employee_id = int(employee_id)
        except (TypeError, ValueError):
            return jsonify({"verified": False, "score": 0.0, "message": "employee_id không hợp lệ."}), 400

        stored = _descriptors_by_employee.get(employee_id)
        if not stored:
            return jsonify({
                "verified": False,
                "score": 0.0,
                "message": "Nhân viên chưa đăng ký khuôn mặt.",
            }), 422

        frame = _decode_image(str(image_base64))
        if frame is None:
            return jsonify({"verified": False, "score": 0.0, "message": "Ảnh không hợp lệ."}), 400

        frame = _resize_frame(frame)
        faces = _engine.detect(frame)
        face = _engine.largest_face(faces)
        if face is None:
            return jsonify({"verified": False, "score": 0.0, "message": "Không thấy khuôn mặt trong khung hình."}), 422

        query = l2_normalize(face.embedding)
        matrix = np.vstack(stored)
        scores = matrix @ query
        best_score = float(np.max(scores))

        verified = best_score >= _config.cosine_threshold
        if verified:
            message = "Xác thực thành công."
        else:
            pct = int(best_score * 100)
            need = int(_config.cosine_threshold * 100)
            message = f"Khuôn mặt không khớp ({pct}%, cần ≥{need}%). Thử nhìn thẳng hơn hoặc đăng ký lại mặt."

        return jsonify({
            "verified": verified,
            "score": round(best_score, 4),
            "message": message,
        })
    finally:
        _infer_lock.release()


@app.post("/enroll/extract-batch")
def enroll_extract_batch():
    assert _engine is not None

    if not _infer_lock.acquire(blocking=False):
        return jsonify({
            "success": False,
            "message": "Máy chủ đang xử lý ảnh trước, vui lòng chờ...",
        }), 429

    try:
        data = request.get_json(silent=True) or {}
        images = data.get("images") or []

        if not isinstance(images, list) or not images:
            return jsonify({"success": False, "message": "Thiếu ảnh mẫu."}), 400

        embeddings: list[np.ndarray] = []
        last_image_base64: str | None = None

        for image_base64 in images:
            frame = _decode_image(str(image_base64))
            if frame is None:
                continue

            frame = _resize_frame(frame)
            faces = _engine.detect(frame)
            face = _engine.largest_face(faces)
            if face is None:
                continue

            embeddings.append(l2_normalize(face.embedding))
            last_image_base64 = str(image_base64)

        if not embeddings:
            return jsonify({
                "success": False,
                "message": "Không thấy khuôn mặt trong các mẫu. Hãy nhìn thẳng vào camera.",
            }), 422

        mean_embedding = l2_normalize(np.mean(np.vstack(embeddings), axis=0))

        return jsonify({
            "success": True,
            "embedding": mean_embedding.tolist(),
            "sample_count": len(embeddings),
            "image_base64": last_image_base64,
            "message": f"Đã trích xuất {len(embeddings)} mẫu khuôn mặt.",
        })
    finally:
        _infer_lock.release()


def main() -> int:
    _init()
    port = _config.api_port  # type: ignore[union-attr]
    laravel_url = _config.laravel_base_url.rstrip("/")  # type: ignore[union-attr]
    print(f"Face API đang chạy tại http://127.0.0.1:{port}")
    print(f"⚠️  KHÔNG mở {port} để chấm công — mở trang Laravel:")
    print(f"    {laravel_url}/employee/attendance")

    def _background_sync() -> None:
        while True:
            time.sleep(_config.sync_interval_seconds)  # type: ignore[union-attr]
            try:
                _sync_descriptors(force=True)
            except Exception as exc:  # noqa: BLE001
                print(f"[sync] Lỗi đồng bộ: {exc}")

    threading.Thread(target=_background_sync, daemon=True).start()
    app.run(host="127.0.0.1", port=port, threaded=True, debug=False)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
