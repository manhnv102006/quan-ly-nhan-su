# Face Service - Chấm công bằng khuôn mặt (kiosk)

Ứng dụng Python chạy tại **máy chấm công (kiosk)**: mở webcam bằng OpenCV,
nhận diện khuôn mặt bằng InsightFace (`buffalo_l`), rồi gọi API Laravel để
đồng bộ dữ liệu khuôn mặt và ghi nhận chấm công.

## Kiến trúc

```
Webcam -> kiosk.py (OpenCV + InsightFace) -> API Laravel -> EmployeeAttendanceService -> bảng attendances
enroll.py -> API Laravel (lưu embedding khuôn mặt cho nhân viên)
```

Nguyên tắc: Python tính embedding + so khớp (cosine). Laravel chỉ lưu embedding
(vector 512 số), xác thực token và ghi chấm công qua service sẵn có.

## Cài đặt

Chạy tại thư mục gốc dự án (đã có sẵn `requirements.txt`):

```powershell
python -m venv venv
.\venv\Scripts\activate
pip install -r requirements.txt
```

Lần chạy đầu, InsightFace sẽ tự tải model `buffalo_l` về `~/.insightface/models`.

## Cấu hình

1. Sao chép `face-service/.env.example` thành `face-service/.env`.
2. Đặt `FACE_KIOSK_TOKEN` **trùng** với giá trị trong `.env` của Laravel.
3. Đặt `LARAVEL_BASE_URL` trỏ tới server Laravel (ví dụ `http://localhost`).

## Sử dụng

### Đăng ký khuôn mặt cho nhân viên

```powershell
python face-service/enroll.py --employee-code NV001 --samples 5
```

Nhìn vào camera, nhấn SPACE để chụp mẫu, ENTER để gửi.

### Chạy kiosk chấm công

```powershell
python face-service/kiosk.py
```

Nhấn `Q` để thoát.

## Các giai đoạn đã triển khai

| Giai đoạn | Tính năng |
|-----------|-----------|
| 1 | Đăng ký khuôn mặt, nhận diện, chấm công check-in/check-out |
| 2 | Nhận diện nhiều người cùng lúc, cooldown từng người |
| 3 | Chống ảnh in (liveness thụ động: MiniFASNet ONNX hoặc heuristic) |
| 4 | Chống video giả (liveness chủ động: yêu cầu quay đầu, bật bằng `FACE_ACTIVE_LIVENESS_ENABLED=true`) |
| 5 | Thông báo sau chấm công, báo cáo tháng + xuất PDF trên admin |

## Model chống giả mạo (tuỳ chọn)

Đặt file `.onnx` MiniFASNet vào thư mục `face-service/models/anti_spoof/` và cấu hình
`FACE_LIVENESS_MODEL_DIR`. Nếu không có model, hệ thống dùng heuristic dự phòng.

## Ghi chú

- `venv/` và `face-service/.env` không được đẩy lên Git.
- Kiosk cần cùng mạng với server Laravel.
