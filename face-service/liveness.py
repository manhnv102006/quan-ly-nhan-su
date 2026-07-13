"""Chống giả mạo (liveness) thụ động — chống tấn công bằng ảnh in / màn hình.

Hai chế độ:
1. Model MiniFASNet (ONNX) của Silent-Face-Anti-Spoofing nếu người dùng đặt
   các file `.onnx` vào thư mục cấu hình (FACE_LIVENESS_MODEL_DIR). Đây là
   phương án chính xác, khuyến nghị cho môi trường thật.
2. Heuristic dự phòng (khi không có model): kết hợp độ nét, phản xạ chói và
   năng lượng tần số cao (moiré) để ước lượng. Chỉ mang tính cơ bản.

Tên file model MiniFASNet cần chứa scale và kích thước, ví dụ:
    2.7_80x80_MiniFASNetV2.onnx
    4_0_0_80x80_MiniFASNetV1SE.onnx
"""

from __future__ import annotations

import glob
import os
import re
from dataclasses import dataclass

import cv2
import numpy as np


@dataclass
class LivenessResult:
    score: float          # xác suất là người thật, 0..1
    is_real: bool
    source: str           # 'onnx' | 'heuristic' | 'disabled'


def _softmax(x: np.ndarray) -> np.ndarray:
    x = x - np.max(x)
    e = np.exp(x)
    return e / np.sum(e)


def _crop_with_scale(image: np.ndarray, bbox: tuple[int, int, int, int], scale: float, out_w: int, out_h: int) -> np.ndarray:
    """Cắt vùng mặt theo thuật toán CropImage của Silent-Face-Anti-Spoofing."""

    src_h, src_w = image.shape[:2]
    x1, y1, x2, y2 = bbox
    box_w = max(1, x2 - x1)
    box_h = max(1, y2 - y1)

    scale = min((src_h - 1) / box_h, min((src_w - 1) / box_w, scale))

    new_width = box_w * scale
    new_height = box_h * scale
    center_x = box_w / 2 + x1
    center_y = box_h / 2 + y1

    left_top_x = center_x - new_width / 2
    left_top_y = center_y - new_height / 2
    right_bottom_x = center_x + new_width / 2
    right_bottom_y = center_y + new_height / 2

    if left_top_x < 0:
        right_bottom_x -= left_top_x
        left_top_x = 0
    if left_top_y < 0:
        right_bottom_y -= left_top_y
        left_top_y = 0
    if right_bottom_x > src_w - 1:
        left_top_x -= (right_bottom_x - src_w + 1)
        right_bottom_x = src_w - 1
    if right_bottom_y > src_h - 1:
        left_top_y -= (right_bottom_y - src_h + 1)
        right_bottom_y = src_h - 1

    left_top_x = int(max(0, left_top_x))
    left_top_y = int(max(0, left_top_y))
    right_bottom_x = int(min(src_w, right_bottom_x))
    right_bottom_y = int(min(src_h, right_bottom_y))

    crop = image[left_top_y:right_bottom_y, left_top_x:right_bottom_x]
    if crop.size == 0:
        crop = image
    return cv2.resize(crop, (out_w, out_h))


class _OnnxMiniFasnet:
    """Bộ liveness dựa trên một hoặc nhiều model MiniFASNet (.onnx)."""

    _NAME_RE = re.compile(r"(\d+(?:[._]\d+)*)_(\d+)x(\d+)")

    def __init__(self, model_dir: str, providers: tuple[str, ...]) -> None:
        import onnxruntime as ort  # nhập tại đây để tránh phụ thuộc khi không dùng

        self._sessions: list[tuple] = []  # (session, input_name, scale, w, h)
        for path in sorted(glob.glob(os.path.join(model_dir, "*.onnx"))):
            scale, w, h = self._parse_name(os.path.basename(path))
            session = ort.InferenceSession(path, providers=list(providers))
            input_name = session.get_inputs()[0].name
            self._sessions.append((session, input_name, scale, w, h))

        if not self._sessions:
            raise FileNotFoundError(f"Không tìm thấy model .onnx trong {model_dir}")

    def _parse_name(self, filename: str) -> tuple[float, int, int]:
        match = self._NAME_RE.search(filename)
        if not match:
            return 2.7, 80, 80
        scale = float(match.group(1).replace("_", "."))
        return scale, int(match.group(2)), int(match.group(3))

    def predict(self, frame_bgr: np.ndarray, bbox: tuple[int, int, int, int]) -> float:
        total = np.zeros(3, dtype=np.float32)

        for session, input_name, scale, w, h in self._sessions:
            crop = _crop_with_scale(frame_bgr, bbox, scale, w, h)
            blob = crop.astype(np.float32).transpose(2, 0, 1)[np.newaxis, ...] / 255.0
            output = session.run(None, {input_name: blob})[0]
            total += _softmax(np.asarray(output).ravel()[:3])

        probs = total / len(self._sessions)
        # Nhãn 1 = thật theo quy ước Silent-Face.
        return float(probs[1])


def _heuristic_score(frame_bgr: np.ndarray, bbox: tuple[int, int, int, int]) -> float:
    """Ước lượng liveness cơ bản khi không có model ONNX."""

    x1, y1, x2, y2 = bbox
    x1, y1 = max(0, x1), max(0, y1)
    crop = frame_bgr[y1:y2, x1:x2]
    if crop.size == 0:
        return 0.0

    gray = cv2.cvtColor(crop, cv2.COLOR_BGR2GRAY)

    # 1. Độ nét (Laplacian variance) — ảnh in/màn hình ở xa thường kém nét.
    sharpness = cv2.Laplacian(gray, cv2.CV_64F).var()
    sharp_score = float(np.clip(sharpness / 150.0, 0.0, 1.0))

    # 2. Phản xạ chói (glare của màn hình / ảnh bóng).
    hsv = cv2.cvtColor(crop, cv2.COLOR_BGR2HSV)
    glare = np.mean((hsv[:, :, 2] > 240) & (hsv[:, :, 1] < 30))
    glare_score = float(np.clip(1.0 - glare * 8.0, 0.0, 1.0))

    # 3. Năng lượng tần số cao (moiré của màn hình tạo đỉnh tần số bất thường).
    f = np.fft.fftshift(np.fft.fft2(gray))
    magnitude = np.abs(f)
    h, w = magnitude.shape
    cy, cx = h // 2, w // 2
    r = max(1, min(h, w) // 8)
    low = magnitude[cy - r:cy + r, cx - r:cx + r].sum()
    high = magnitude.sum() - low
    high_ratio = high / (magnitude.sum() + 1e-6)
    # Ảnh thật có phổ tần số cao vừa phải; quá thấp (in mờ) hoặc quá cao (moiré) đều đáng ngờ.
    freq_score = float(np.clip(1.0 - abs(high_ratio - 0.5) * 2.0, 0.0, 1.0))

    return float(np.clip(0.5 * sharp_score + 0.25 * glare_score + 0.25 * freq_score, 0.0, 1.0))


class LivenessDetector:
    def __init__(
        self,
        enabled: bool,
        threshold: float,
        model_dir: str,
        providers: tuple[str, ...] = ("CPUExecutionProvider",),
    ) -> None:
        self.enabled = enabled
        self.threshold = threshold
        self._onnx: _OnnxMiniFasnet | None = None

        if enabled:
            try:
                self._onnx = _OnnxMiniFasnet(model_dir, providers)
                print(f"[liveness] Dùng model ONNX trong {model_dir}")
            except Exception as exc:  # noqa: BLE001
                print(f"[liveness] Không có model ONNX ({exc}). Dùng heuristic dự phòng.")

    def check(self, frame_bgr: np.ndarray, bbox: tuple[int, int, int, int]) -> LivenessResult:
        if not self.enabled:
            return LivenessResult(score=1.0, is_real=True, source="disabled")

        if self._onnx is not None:
            score = self._onnx.predict(frame_bgr, bbox)
            source = "onnx"
        else:
            score = _heuristic_score(frame_bgr, bbox)
            source = "heuristic"

        return LivenessResult(score=score, is_real=score >= self.threshold, source=source)
