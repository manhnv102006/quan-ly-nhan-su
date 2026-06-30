# TODO - Manager chấm KPI cho Employee

## Step 1: Kiểm tra schema (score/comment) của `employee_kpis`
- [x] Xác nhận: `score` decimal nullable (5,2), `comment` text nullable.


## Step 2: Cập nhật routes cho Manager KPI score
- [ ] Thêm route GET/PUT cho màn hình chấm KPI (theo từng `EmployeeKPI`).


## Step 3: Cập nhật Controller
- [ ] Bổ sung method để hiển thị form chấm KPI.
- [ ] Bổ sung method để lưu score/comment chỉ cập nhật 2 trường.
- [ ] Authorization: Manager chỉ sửa KPI thuộc KPIAssignment của chính mình.

## Step 4: Tạo Form Request
- [ ] Validate `score` trong [0..100] và `comment` (nullable, max theo schema).

## Step 5: Tạo View
- [ ] Tạo form `resources/views/manager/kpis/score.blade.php`.

## Step 6: Cập nhật view danh sách chi tiết KPI
- [ ] Update `resources/views/manager/kpis/show.blade.php`: nút "Chấm KPI" link đúng.

## Step 7: Thử nghiệm
- [ ] Chạy route list (kiểm tra route mới).
- [ ] Test trên UI: manager -> list -> click chấm -> submit -> back list + flash "Chấm KPI thành công".

