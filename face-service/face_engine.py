"""Bọc InsightFace để phát hiện + trích xuất embedding + so khớp khuôn mặt.

Sử dụng model `buffalo_l` (đã kiểm tra chạy được với onnxruntime CPU).
Embedding trả về đã được chuẩn hoá L2 để so khớp bằng tích vô hướng (cosine).
"""

from __future__ import annotations

from dataclasses import dataclass

import numpy as np
from insightface.app import FaceAnalysis


@dataclass
class DetectedFace:
    """Một khuôn mặt phát hiện trong khung hình."""

    bbox: np.ndarray            # [x1, y1, x2, y2]
    det_score: float
    embedding: np.ndarray       # vector 512 chiều, đã chuẩn hoá L2
    kps: np.ndarray | None = None  # 5 điểm mốc (landmark)

    @property
    def bbox_int(self) -> tuple[int, int, int, int]:
        x1, y1, x2, y2 = self.bbox.astype(int)
        return int(x1), int(y1), int(x2), int(y2)


@dataclass
class GalleryEntry:
    """Một nhân viên trong thư viện khuôn mặt đã đăng ký."""

    employee_id: int
    full_name: str
    employee_code: str
    embedding: np.ndarray  # đã chuẩn hoá L2


@dataclass
class MatchResult:
    employee_id: int
    full_name: str
    employee_code: str
    score: float


def l2_normalize(vector: np.ndarray) -> np.ndarray:
    vector = np.asarray(vector, dtype=np.float32).ravel()
    norm = np.linalg.norm(vector)
    if norm == 0:
        return vector
    return vector / norm


class FaceEngine:
    def __init__(
        self,
        model_name: str = "buffalo_l",
        det_size: tuple[int, int] = (640, 640),
        providers: tuple[str, ...] = ("CPUExecutionProvider",),
        min_det_score: float = 0.5,
    ) -> None:
        self.min_det_score = min_det_score
        self._app = FaceAnalysis(name=model_name, providers=list(providers))
        # ctx_id=-1 để chạy CPU; det_size là kích thước ảnh dùng cho bộ phát hiện.
        self._app.prepare(ctx_id=-1, det_size=det_size)

    def detect(self, frame_bgr: np.ndarray) -> list[DetectedFace]:
        """Phát hiện tất cả khuôn mặt trong khung hình (ảnh BGR của OpenCV)."""

        faces = self._app.get(frame_bgr)
        results: list[DetectedFace] = []

        for face in faces:
            if float(face.det_score) < self.min_det_score:
                continue

            results.append(
                DetectedFace(
                    bbox=np.asarray(face.bbox, dtype=np.float32),
                    det_score=float(face.det_score),
                    embedding=l2_normalize(face.embedding),
                    kps=np.asarray(face.kps, dtype=np.float32) if face.kps is not None else None,
                )
            )

        return results

    def largest_face(self, faces: list[DetectedFace]) -> DetectedFace | None:
        """Trả về khuôn mặt lớn nhất (gần camera nhất)."""

        if not faces:
            return None

        def area(face: DetectedFace) -> float:
            x1, y1, x2, y2 = face.bbox
            return float(max(0.0, x2 - x1) * max(0.0, y2 - y1))

        return max(faces, key=area)

    @staticmethod
    def cosine_similarity(a: np.ndarray, b: np.ndarray) -> float:
        """Cosine giữa hai vector đã chuẩn hoá L2 = tích vô hướng."""

        return float(np.dot(l2_normalize(a), l2_normalize(b)))

    def match(
        self,
        embedding: np.ndarray,
        gallery: list[GalleryEntry],
        threshold: float,
    ) -> MatchResult | None:
        """Tìm nhân viên khớp nhất trong thư viện; None nếu dưới ngưỡng."""

        if not gallery:
            return None

        query = l2_normalize(embedding)
        gallery_matrix = np.vstack([entry.embedding for entry in gallery])
        scores = gallery_matrix @ query  # tích vô hướng vì đã chuẩn hoá

        best_index = int(np.argmax(scores))
        best_score = float(scores[best_index])

        if best_score < threshold:
            return None

        best = gallery[best_index]
        return MatchResult(
            employee_id=best.employee_id,
            full_name=best.full_name,
            employee_code=best.employee_code,
            score=best_score,
        )
