"""Cấu hình dùng chung cho các công cụ nhận diện khuôn mặt (kiosk + enroll).

Đọc cấu hình từ file `.env` nằm cùng thư mục `face-service/` (nếu có),
sau đó tới biến môi trường của hệ thống. Không phụ thuộc python-dotenv để
giữ danh sách thư viện gọn nhẹ.
"""

from __future__ import annotations

import os
import sys
from dataclasses import dataclass
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent
ENV_PATH = BASE_DIR / ".env"


def configure_console() -> None:
    """Ép stdout/stderr sang UTF-8 để in được tiếng Việt trên console Windows."""

    for stream in (sys.stdout, sys.stderr):
        try:
            stream.reconfigure(encoding="utf-8", errors="replace")  # type: ignore[attr-defined]
        except (AttributeError, ValueError):
            pass


def _load_env_file(path: Path) -> None:
    """Nạp các cặp KEY=VALUE trong file .env vào os.environ (không ghi đè sẵn có)."""

    if not path.exists():
        return

    for raw_line in path.read_text(encoding="utf-8").splitlines():
        line = raw_line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue

        key, _, value = line.partition("=")
        key = key.strip()
        value = value.strip().strip('"').strip("'")

        if key and key not in os.environ:
            os.environ[key] = value


def _get(key: str, default: str) -> str:
    return os.environ.get(key, default)


def _get_int(key: str, default: int) -> int:
    try:
        return int(os.environ.get(key, default))
    except (TypeError, ValueError):
        return default


def _get_float(key: str, default: float) -> float:
    try:
        return float(os.environ.get(key, default))
    except (TypeError, ValueError):
        return default


def _get_bool(key: str, default: bool) -> bool:
    value = os.environ.get(key)
    if value is None:
        return default
    return value.strip().lower() in {"1", "true", "yes", "on"}


@dataclass(frozen=True)
class Config:
    """Toàn bộ tham số cấu hình cho công cụ nhận diện khuôn mặt."""

    laravel_base_url: str
    kiosk_token: str

    # InsightFace
    model_name: str
    det_size: int
    providers: tuple[str, ...]

    # Camera
    camera_index: int

    # Nhận diện / so khớp
    cosine_threshold: float
    min_det_score: float

    # Vòng lặp kiosk
    sync_interval_seconds: int
    cooldown_seconds: int

    # Chống giả mạo (liveness)
    liveness_enabled: bool
    liveness_threshold: float
    liveness_model_dir: str

    # Chống video giả (liveness chủ động: yêu cầu quay đầu)
    active_liveness_enabled: bool
    active_window_seconds: float
    yaw_delta_threshold: float

    # HTTP
    request_timeout: int

    @property
    def det_size_tuple(self) -> tuple[int, int]:
        return (self.det_size, self.det_size)


def load_config() -> Config:
    _load_env_file(ENV_PATH)

    providers_raw = _get("FACE_PROVIDERS", "CPUExecutionProvider")
    providers = tuple(p.strip() for p in providers_raw.split(",") if p.strip())

    default_model_dir = str(BASE_DIR / "models" / "anti_spoof")
    model_dir = _get("FACE_LIVENESS_MODEL_DIR", default_model_dir).strip() or default_model_dir

    return Config(
        laravel_base_url=_get("LARAVEL_BASE_URL", "http://localhost").rstrip("/"),
        kiosk_token=_get("FACE_KIOSK_TOKEN", ""),
        model_name=_get("FACE_MODEL_NAME", "buffalo_l"),
        det_size=_get_int("FACE_DET_SIZE", 640),
        providers=providers or ("CPUExecutionProvider",),
        camera_index=_get_int("FACE_CAMERA_INDEX", 0),
        cosine_threshold=_get_float("FACE_COSINE_THRESHOLD", 0.35),
        min_det_score=_get_float("FACE_MIN_DET_SCORE", 0.5),
        sync_interval_seconds=_get_int("FACE_SYNC_INTERVAL", 120),
        cooldown_seconds=_get_int("FACE_COOLDOWN_SECONDS", 30),
        liveness_enabled=_get_bool("FACE_LIVENESS_ENABLED", True),
        liveness_threshold=_get_float("FACE_LIVENESS_THRESHOLD", 0.5),
        liveness_model_dir=model_dir,
        active_liveness_enabled=_get_bool("FACE_ACTIVE_LIVENESS_ENABLED", False),
        active_window_seconds=_get_float("FACE_ACTIVE_WINDOW_SECONDS", 6.0),
        yaw_delta_threshold=_get_float("FACE_YAW_DELTA", 0.35),
        request_timeout=_get_int("FACE_REQUEST_TIMEOUT", 15),
    )
