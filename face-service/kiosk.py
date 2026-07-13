"""Kiosk chấm công bằng khuôn mặt (Giai đoạn 1: nhận diện 1 người/khung).

- Đồng bộ thư viện khuôn mặt từ Laravel (định kỳ).
- Mở webcam, nhận diện khuôn mặt lớn nhất, so khớp cosine.
- Nếu khớp và ngoài thời gian chờ (cooldown), gọi API chấm công.
- Hiển thị tên + kết quả lên màn hình. Nhấn Q để thoát.
"""

from __future__ import annotations

import sys
import time

import cv2

from active_liveness import ActiveLiveness
from config import Config, configure_console, load_config
from face_engine import FaceEngine, GalleryEntry
from laravel_client import LaravelClient
from liveness import LivenessDetector
from ui_utils import put_texts

WINDOW = "Kiosk cham cong - khuon mat"


class GallerySync:
    """Quản lý việc tải và làm mới thư viện khuôn mặt."""

    def __init__(self, client: LaravelClient, interval_seconds: int) -> None:
        self._client = client
        self._interval = interval_seconds
        self._entries: list[GalleryEntry] = []
        self._last_sync = 0.0

    @property
    def entries(self) -> list[GalleryEntry]:
        return self._entries

    def refresh_if_needed(self, force: bool = False) -> None:
        now = time.time()
        if not force and (now - self._last_sync) < self._interval:
            return
        try:
            self._entries = self._client.fetch_gallery()
            self._last_sync = now
            print(f"[sync] Đã tải {len(self._entries)} mẫu khuôn mặt.")
        except Exception as exc:  # noqa: BLE001
            print(f"[sync] Lỗi đồng bộ: {exc}", file=sys.stderr)


class CooldownTracker:
    """Chống chấm công trùng lặp trong khoảng thời gian ngắn cho từng nhân viên."""

    def __init__(self, cooldown_seconds: int) -> None:
        self._cooldown = cooldown_seconds
        self._last: dict[int, float] = {}

    def is_ready(self, employee_id: int) -> bool:
        now = time.time()
        last = self._last.get(employee_id, 0.0)
        return (now - last) >= self._cooldown

    def mark(self, employee_id: int) -> None:
        self._last[employee_id] = time.time()

    def remaining(self, employee_id: int) -> int:
        now = time.time()
        last = self._last.get(employee_id, 0.0)
        return max(0, int(self._cooldown - (now - last)))


class StatusBanner:
    """Thông báo tạm thời hiển thị dưới màn hình."""

    def __init__(self) -> None:
        self.text = "San sang..."
        self.tone = "idle"
        self._expire = 0.0

    def set(self, text: str, tone: str, seconds: float = 3.0) -> None:
        self.text = text
        self.tone = tone
        self._expire = time.time() + seconds

    def current(self) -> tuple[str, str]:
        if time.time() > self._expire and self.tone != "idle":
            self.tone = "idle"
            self.text = "San sang..."
        return self.text, self.tone


def tone_color(tone: str) -> tuple[int, int, int]:
    return {
        "success": (0, 200, 0),
        "error": (0, 0, 255),
        "warning": (0, 165, 255),
        "idle": (200, 200, 200),
    }.get(tone, (200, 200, 200))


def process_frame(
    frame,
    engine: FaceEngine,
    gallery: GallerySync,
    cooldown: CooldownTracker,
    banner: StatusBanner,
    client: LaravelClient,
    config: Config,
    liveness: LivenessDetector,
    active: ActiveLiveness,
) -> list[dict]:
    """Xử lý TẤT CẢ khuôn mặt trong khung hình (nhận diện nhiều người cùng lúc).

    Vẽ khung từng khuôn mặt (in-place) và trả về danh sách nhãn cần vẽ. Mỗi
    nhân viên được nhận diện sẽ được kiểm tra chống giả mạo rồi chấm công nếu
    đã hết thời gian chờ riêng.
    """

    faces = engine.detect(frame)
    labels: list[dict] = []
    recorded: list[str] = []

    for face in faces:
        x1, y1, x2, y2 = face.bbox_int
        match = engine.match(face.embedding, gallery.entries, config.cosine_threshold)

        if match is None:
            cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 0, 255), 2)
            labels.append(_label_item("Khong nhan dien", (x1, y1), (0, 0, 255)))
            continue

        if not cooldown.is_ready(match.employee_id):
            cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 165, 255), 2)
            text = f"{match.full_name} - cho {cooldown.remaining(match.employee_id)}s"
            labels.append(_label_item(text, (x1, y1), (0, 165, 255)))
            continue

        # Chống giả mạo thụ động: từ chối ảnh in / màn hình trước khi chấm công.
        live = liveness.check(frame, face.bbox_int)
        if not live.is_real:
            cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 0, 255), 2)
            labels.append(_label_item(f"Nghi gia mao ({live.score:.2f})", (x1, y1), (0, 0, 255)))
            banner.set(f"{match.full_name}: nghi ngo gia mao, thu lai", "error")
            continue

        # Chống video giả: yêu cầu quay đầu (liveness chủ động).
        if not active.update(match.employee_id, face.kps):
            cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 165, 255), 2)
            labels.append(_label_item(f"{match.full_name}: {active.prompt(match.employee_id)}", (x1, y1), (0, 165, 255)))
            banner.set(f"{match.full_name}: {active.prompt(match.employee_id)}", "warning")
            continue

        cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 200, 0), 2)

        result = client.record_attendance(
            employee_id=match.employee_id,
            action="auto",
            confidence=match.score,
            liveness_score=live.score,
        )
        cooldown.mark(match.employee_id)

        labels.append(_label_item(f"{match.full_name} ({match.score:.2f})", (x1, y1), (0, 200, 0)))
        recorded.append(f"{match.full_name}: {result.message}")
        print(f"[cham cong] {match.full_name}: {result.message}")

    if recorded:
        if len(recorded) == 1:
            banner.set(recorded[0], "success")
        else:
            banner.set(f"Da cham cong {len(recorded)} nguoi", "success")

    return labels


def _label_item(text: str, org_bbox: tuple[int, int], color: tuple[int, int, int]) -> dict:
    x, y = org_bbox
    return {
        "text": text,
        "org": (x, max(0, y - 28)),
        "color": color,
        "font_size": 20,
        "background": (30, 30, 30),
    }


def main() -> int:
    configure_console()
    config = load_config()
    if not config.kiosk_token:
        print("Thiếu FACE_KIOSK_TOKEN trong face-service/.env", file=sys.stderr)
        return 2

    client = LaravelClient(config.laravel_base_url, config.kiosk_token, config.request_timeout)

    print("Đang khởi tạo InsightFace (lần đầu có thể tải model)...")
    engine = FaceEngine(
        model_name=config.model_name,
        det_size=config.det_size_tuple,
        providers=config.providers,
        min_det_score=config.min_det_score,
    )

    gallery = GallerySync(client, config.sync_interval_seconds)
    gallery.refresh_if_needed(force=True)
    if not gallery.entries:
        print("[canh bao] Chưa có khuôn mặt nào được đăng ký. Hãy chạy enroll.py trước.")

    cooldown = CooldownTracker(config.cooldown_seconds)
    banner = StatusBanner()

    liveness = LivenessDetector(
        enabled=config.liveness_enabled,
        threshold=config.liveness_threshold,
        model_dir=config.liveness_model_dir,
        providers=config.providers,
    )

    active = ActiveLiveness(
        enabled=config.active_liveness_enabled,
        window_seconds=config.active_window_seconds,
        yaw_delta_threshold=config.yaw_delta_threshold,
    )

    cap = cv2.VideoCapture(config.camera_index)
    if not cap.isOpened():
        print(f"Không mở được camera {config.camera_index}", file=sys.stderr)
        return 1

    print("Kiosk đang chạy. Nhấn Q để thoát.")
    try:
        while True:
            ok, frame = cap.read()
            if not ok:
                print("Không đọc được khung hình từ camera.", file=sys.stderr)
                break

            gallery.refresh_if_needed()

            labels = process_frame(frame, engine, gallery, cooldown, banner, client, config, liveness, active)

            text, tone = banner.current()
            overlay_items = [
                {
                    "text": f"Da dang ky: {len(gallery.entries)} | Nhan Q de thoat",
                    "org": (10, 10),
                    "color": (255, 255, 0),
                    "font_size": 20,
                },
                {
                    "text": text,
                    "org": (10, frame.shape[0] - 40),
                    "color": tone_color(tone),
                    "font_size": 24,
                    "background": (20, 20, 20),
                },
            ]
            frame = put_texts(frame, labels + overlay_items)

            cv2.imshow(WINDOW, frame)
            if (cv2.waitKey(1) & 0xFF) == ord("q"):
                break
    finally:
        cap.release()
        cv2.destroyAllWindows()

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
