"""Tiện ích hiển thị: vẽ chữ tiếng Việt lên khung hình và mã hoá ảnh base64."""

from __future__ import annotations

import base64
from functools import lru_cache

import cv2
import numpy as np
from PIL import Image, ImageDraw, ImageFont

# Một số vị trí font phổ biến trên Windows có hỗ trợ tiếng Việt.
_FONT_CANDIDATES = [
    r"C:\Windows\Fonts\arial.ttf",
    r"C:\Windows\Fonts\segoeui.ttf",
    r"C:\Windows\Fonts\tahoma.ttf",
]


@lru_cache(maxsize=8)
def _load_font(size: int) -> ImageFont.FreeTypeFont:
    for path in _FONT_CANDIDATES:
        try:
            return ImageFont.truetype(path, size)
        except OSError:
            continue
    return ImageFont.load_default()


def put_text(
    frame_bgr: np.ndarray,
    text: str,
    org: tuple[int, int],
    color: tuple[int, int, int] = (255, 255, 255),
    font_size: int = 22,
    background: tuple[int, int, int] | None = None,
) -> np.ndarray:
    """Vẽ chuỗi Unicode (tiếng Việt) lên ảnh BGR, trả về ảnh mới."""

    image = Image.fromarray(cv2.cvtColor(frame_bgr, cv2.COLOR_BGR2RGB))
    draw = ImageDraw.Draw(image)
    font = _load_font(font_size)

    x, y = org
    if background is not None:
        bbox = draw.textbbox((x, y), text, font=font)
        pad = 4
        draw.rectangle(
            (bbox[0] - pad, bbox[1] - pad, bbox[2] + pad, bbox[3] + pad),
            fill=(background[2], background[1], background[0]),
        )

    # PIL dùng RGB, tham số color truyền vào theo BGR để đồng bộ với OpenCV.
    draw.text((x, y), text, font=font, fill=(color[2], color[1], color[0]))

    return cv2.cvtColor(np.array(image), cv2.COLOR_RGB2BGR)


def put_texts(frame_bgr: np.ndarray, items: list[dict]) -> np.ndarray:
    """Vẽ nhiều chuỗi trong một lần chuyển đổi PIL (hiệu quả hơn gọi put_text nhiều lần).

    Mỗi item: {text, org=(x,y), color=(B,G,R), font_size, background=(B,G,R)|None}
    """

    if not items:
        return frame_bgr

    image = Image.fromarray(cv2.cvtColor(frame_bgr, cv2.COLOR_BGR2RGB))
    draw = ImageDraw.Draw(image)

    for item in items:
        text = item["text"]
        x, y = item["org"]
        color = item.get("color", (255, 255, 255))
        font = _load_font(item.get("font_size", 22))
        background = item.get("background")

        if background is not None:
            bbox = draw.textbbox((x, y), text, font=font)
            pad = 4
            draw.rectangle(
                (bbox[0] - pad, bbox[1] - pad, bbox[2] + pad, bbox[3] + pad),
                fill=(background[2], background[1], background[0]),
            )

        draw.text((x, y), text, font=font, fill=(color[2], color[1], color[0]))

    return cv2.cvtColor(np.array(image), cv2.COLOR_RGB2BGR)


def encode_jpeg_base64(image_bgr: np.ndarray, quality: int = 85) -> str | None:
    """Mã hoá ảnh BGR thành chuỗi JPEG base64 (không kèm tiền tố data:)."""

    ok, buffer = cv2.imencode(".jpg", image_bgr, [int(cv2.IMWRITE_JPEG_QUALITY), quality])
    if not ok:
        return None
    return base64.b64encode(buffer.tobytes()).decode("ascii")


def crop_face(frame_bgr: np.ndarray, bbox: tuple[int, int, int, int], margin: float = 0.2) -> np.ndarray:
    """Cắt vùng khuôn mặt kèm lề, giới hạn trong khung hình."""

    h, w = frame_bgr.shape[:2]
    x1, y1, x2, y2 = bbox
    bw, bh = x2 - x1, y2 - y1
    mx, my = int(bw * margin), int(bh * margin)

    x1 = max(0, x1 - mx)
    y1 = max(0, y1 - my)
    x2 = min(w, x2 + mx)
    y2 = min(h, y2 + my)

    return frame_bgr[y1:y2, x1:x2]
