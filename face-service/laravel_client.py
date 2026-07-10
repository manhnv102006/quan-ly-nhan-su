"""Client HTTP gọi API Laravel cho luồng chấm công bằng khuôn mặt."""

from __future__ import annotations

from dataclasses import dataclass
from typing import Any

import numpy as np
import requests

from face_engine import GalleryEntry, l2_normalize


@dataclass
class AttendanceResult:
    ok: bool
    message: str
    action: str | None = None
    status_code: int | None = None


class LaravelClient:
    def __init__(self, base_url: str, token: str, timeout: int = 15) -> None:
        self.base_url = base_url.rstrip("/")
        self.timeout = timeout
        self._session = requests.Session()
        self._session.headers.update(
            {
                "X-Face-Token": token,
                "Accept": "application/json",
            }
        )

    def _url(self, path: str) -> str:
        return f"{self.base_url}/{path.lstrip('/')}"

    def fetch_employees(self) -> list[dict[str, Any]]:
        """Tải danh sách nhân viên đang làm việc (id, tên, mã, đã đăng ký mặt)."""

        response = self._session.get(
            self._url("/api/face/employees"),
            timeout=self.timeout,
        )
        response.raise_for_status()
        return response.json().get("data", [])

    def fetch_gallery(self) -> list[GalleryEntry]:
        """Tải danh sách khuôn mặt đã đăng ký từ Laravel."""

        response = self._session.get(
            self._url("/api/face/descriptors"),
            timeout=self.timeout,
        )
        response.raise_for_status()
        payload = response.json()

        entries: list[GalleryEntry] = []
        for item in payload.get("data", []):
            embedding = item.get("embedding")
            if not embedding:
                continue

            entries.append(
                GalleryEntry(
                    employee_id=int(item["employee_id"]),
                    full_name=str(item.get("full_name", "")),
                    employee_code=str(item.get("employee_code", "")),
                    embedding=l2_normalize(np.asarray(embedding, dtype=np.float32)),
                )
            )

        return entries

    def enroll_descriptor(
        self,
        employee_id: int,
        embedding: np.ndarray,
        image_base64: str | None = None,
        quality: float | None = None,
    ) -> dict[str, Any]:
        """Gửi một mẫu khuôn mặt (embedding) lên Laravel để lưu."""

        body: dict[str, Any] = {
            "employee_id": employee_id,
            "embedding": l2_normalize(embedding).tolist(),
        }
        if image_base64 is not None:
            body["image_base64"] = image_base64
        if quality is not None:
            body["quality"] = quality

        response = self._session.post(
            self._url("/api/face/descriptors"),
            json=body,
            timeout=self.timeout,
        )
        response.raise_for_status()
        return response.json()

    def record_attendance(
        self,
        employee_id: int,
        action: str = "auto",
        confidence: float | None = None,
        liveness_score: float | None = None,
        image_base64: str | None = None,
    ) -> AttendanceResult:
        """Ghi nhận chấm công. action: auto | check-in | check-out."""

        body: dict[str, Any] = {
            "employee_id": employee_id,
            "action": action,
        }
        if confidence is not None:
            body["confidence"] = round(float(confidence), 4)
        if liveness_score is not None:
            body["liveness_score"] = round(float(liveness_score), 4)
        if image_base64 is not None:
            body["image_base64"] = image_base64

        try:
            response = self._session.post(
                self._url("/api/face/attendance"),
                json=body,
                timeout=self.timeout,
            )
        except requests.RequestException as exc:
            return AttendanceResult(ok=False, message=f"Lỗi kết nối: {exc}")

        try:
            payload = response.json()
        except ValueError:
            payload = {}

        message = payload.get("message", "") or f"HTTP {response.status_code}"
        return AttendanceResult(
            ok=response.ok and bool(payload.get("success", response.ok)),
            message=message,
            action=payload.get("action"),
            status_code=response.status_code,
        )
