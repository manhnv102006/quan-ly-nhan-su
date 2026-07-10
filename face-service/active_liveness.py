"""Chống video giả bằng liveness CHỦ ĐỘNG: yêu cầu người dùng quay đầu.

Ý tưởng: ảnh in tĩnh và nhiều video phát lại không tạo được chuyển động
quay đầu tự nhiên. Ta ước lượng góc quay (yaw) từ 5 điểm mốc của InsightFace
(2 mắt, mũi, 2 khoé miệng) và yêu cầu người dùng quay đầu qua trái/phải đủ
biên độ trong một khoảng thời gian ngắn thì mới xác nhận là người thật.

Trạng thái được lưu theo từng employee_id nên hoạt động với nhiều người.
"""

from __future__ import annotations

import time
from collections import deque

import numpy as np


def estimate_yaw(kps: np.ndarray | None) -> float | None:
    """Ước lượng chỉ số yaw (âm/dương = quay trái/phải) từ 5 keypoints.

    kps theo thứ tự InsightFace: [mắt trái, mắt phải, mũi, miệng trái, miệng phải].
    Trả về offset ngang của mũi so với trung điểm hai mắt, chuẩn hoá theo
    khoảng cách hai mắt. Giá trị ~0 khi nhìn thẳng.
    """

    if kps is None or len(kps) < 3:
        return None

    left_eye, right_eye, nose = kps[0], kps[1], kps[2]
    eye_mid_x = (left_eye[0] + right_eye[0]) / 2.0
    inter_ocular = float(np.hypot(right_eye[0] - left_eye[0], right_eye[1] - left_eye[1]))
    if inter_ocular < 1e-3:
        return None

    return float((nose[0] - eye_mid_x) / inter_ocular)


class ActiveLiveness:
    """Theo dõi chuyển động quay đầu theo từng nhân viên."""

    def __init__(self, enabled: bool, window_seconds: float, yaw_delta_threshold: float) -> None:
        self.enabled = enabled
        self.window = window_seconds
        self.yaw_delta = yaw_delta_threshold
        self._history: dict[int, deque] = {}
        self._confirmed_until: dict[int, float] = {}

    def reset(self, employee_id: int) -> None:
        self._history.pop(employee_id, None)
        self._confirmed_until.pop(employee_id, None)

    def update(self, employee_id: int, kps: np.ndarray | None) -> bool:
        """Cập nhật lịch sử yaw và trả về True nếu đã xác nhận quay đầu."""

        if not self.enabled:
            return True

        now = time.time()

        # Còn hiệu lực xác nhận gần đây (cho tới khi chấm công xong).
        if self._confirmed_until.get(employee_id, 0.0) > now:
            return True

        yaw = estimate_yaw(kps)
        if yaw is None:
            return False

        history = self._history.setdefault(employee_id, deque())
        history.append((now, yaw))

        # Loại bỏ mẫu cũ ngoài cửa sổ thời gian.
        while history and (now - history[0][0]) > self.window:
            history.popleft()

        yaws = [y for _, y in history]
        if len(yaws) >= 3 and (max(yaws) - min(yaws)) >= self.yaw_delta:
            # Xác nhận và giữ hiệu lực 3 giây để kịp ghi chấm công.
            self._confirmed_until[employee_id] = now + 3.0
            history.clear()
            return True

        return False

    def prompt(self, employee_id: int) -> str:
        """Gợi ý hành động cho người dùng."""

        return "Hay quay dau trai/phai"
