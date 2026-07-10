"""Công cụ đăng ký khuôn mặt cho nhân viên qua webcam.

Cách dùng:
    python face-service/enroll.py --employee-code NV001 --samples 5
    python face-service/enroll.py --employee-id 12 --samples 5

Nhìn vào camera, nhấn SPACE để chụp một mẫu, nhấn ENTER để gửi khi đủ mẫu,
nhấn Q để huỷ. Công cụ lấy embedding trung bình của các mẫu rồi gửi lên Laravel.
"""

from __future__ import annotations

import argparse
import sys

import cv2
import numpy as np

from config import configure_console, load_config
from face_engine import FaceEngine, l2_normalize
from laravel_client import LaravelClient
from ui_utils import crop_face, encode_jpeg_base64, put_text

WINDOW = "Dang ky khuon mat"


def resolve_employee(client: LaravelClient, employee_id: int | None, employee_code: str | None) -> dict:
    employees = client.fetch_employees()

    if employee_id is not None:
        for emp in employees:
            if int(emp["employee_id"]) == employee_id:
                return emp
        raise SystemExit(f"Không tìm thấy nhân viên có id={employee_id} (hoặc đang không active).")

    code = (employee_code or "").strip().lower()
    for emp in employees:
        if str(emp["employee_code"]).lower() == code:
            return emp

    raise SystemExit(f"Không tìm thấy nhân viên có mã '{employee_code}' (hoặc đang không active).")


def main() -> int:
    parser = argparse.ArgumentParser(description="Đăng ký khuôn mặt nhân viên.")
    group = parser.add_mutually_exclusive_group(required=True)
    group.add_argument("--employee-code", help="Mã nhân viên, ví dụ NV001")
    group.add_argument("--employee-id", type=int, help="ID nhân viên")
    parser.add_argument("--samples", type=int, default=5, help="Số mẫu cần chụp (mặc định 5)")
    parser.add_argument("--camera", type=int, default=None, help="Chỉ số camera (ghi đè cấu hình)")
    args = parser.parse_args()

    configure_console()
    config = load_config()
    if not config.kiosk_token:
        print("Thiếu FACE_KIOSK_TOKEN trong face-service/.env", file=sys.stderr)
        return 2

    client = LaravelClient(config.laravel_base_url, config.kiosk_token, config.request_timeout)

    print("Đang tra cứu nhân viên...")
    employee = resolve_employee(client, args.employee_id, args.employee_code)
    print(f"Nhân viên: {employee['full_name']} ({employee['employee_code']}) - id={employee['employee_id']}")

    print("Đang khởi tạo InsightFace (lần đầu có thể tải model)...")
    engine = FaceEngine(
        model_name=config.model_name,
        det_size=config.det_size_tuple,
        providers=config.providers,
        min_det_score=config.min_det_score,
    )

    camera_index = args.camera if args.camera is not None else config.camera_index
    cap = cv2.VideoCapture(camera_index)
    if not cap.isOpened():
        print(f"Không mở được camera {camera_index}", file=sys.stderr)
        return 1

    collected: list[np.ndarray] = []
    last_crop = None
    print("SPACE = chụp mẫu | ENTER = gửi | Q = huỷ")

    try:
        while True:
            ok, frame = cap.read()
            if not ok:
                print("Không đọc được khung hình từ camera.", file=sys.stderr)
                break

            faces = engine.detect(frame)
            face = engine.largest_face(faces)

            display = frame.copy()
            if face is not None:
                x1, y1, x2, y2 = face.bbox_int
                cv2.rectangle(display, (x1, y1), (x2, y2), (0, 200, 0), 2)

            display = put_text(
                display,
                f"{employee['full_name']} | Mau: {len(collected)}/{args.samples}",
                (10, 10),
                color=(0, 255, 0),
                font_size=22,
            )
            hint = "SPACE: chup  ENTER: gui  Q: huy"
            display = put_text(display, hint, (10, display.shape[0] - 34), color=(255, 255, 0), font_size=20)

            cv2.imshow(WINDOW, display)
            key = cv2.waitKey(1) & 0xFF

            if key == ord("q"):
                print("Đã huỷ.")
                return 0

            if key == 32:  # SPACE
                if face is None:
                    print("Chưa thấy khuôn mặt, thử lại.")
                    continue
                collected.append(l2_normalize(face.embedding))
                last_crop = crop_face(frame, face.bbox_int)
                print(f"Đã chụp mẫu {len(collected)}/{args.samples}")

            if key in (13, 10):  # ENTER
                if not collected:
                    print("Chưa có mẫu nào.")
                    continue
                break
    finally:
        cap.release()
        cv2.destroyAllWindows()

    mean_embedding = l2_normalize(np.mean(np.vstack(collected), axis=0))
    image_base64 = encode_jpeg_base64(last_crop) if last_crop is not None else None

    print("Đang gửi mẫu khuôn mặt lên máy chủ...")
    try:
        result = client.enroll_descriptor(
            employee_id=int(employee["employee_id"]),
            embedding=mean_embedding,
            image_base64=image_base64,
            quality=float(len(collected)),
        )
    except Exception as exc:  # noqa: BLE001 - hiển thị lỗi thân thiện
        print(f"Gửi thất bại: {exc}", file=sys.stderr)
        return 1

    print(f"Thành công! {result.get('message', '')} (descriptor_id={result.get('descriptor_id')})")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
