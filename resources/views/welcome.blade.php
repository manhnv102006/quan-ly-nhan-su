<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Nhân Sự</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-blue-600 text-white px-6 py-4 shadow-md">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">👥 Quản Lý Nhân Sự</h1>
            <span class="text-sm">Laravel v13.15.0</span>
        </div>
    </nav>

    <!-- Hero -->
    <div class="max-w-6xl mx-auto mt-10 px-4">
        <div class="bg-white rounded-2xl shadow p-8 text-center">
            <h2 class="text-3xl font-bold text-blue-600 mb-2">Hệ Thống Quản Lý Nhân Sự</h2>
            <p class="text-gray-500 mb-6">Quản lý nhân viên, phòng ban, chấm công dễ dàng</p>
            <button class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                Bắt đầu ngay
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-6 mt-8">
            <div class="bg-white rounded-2xl shadow p-6 text-center">
                <p class="text-4xl font-bold text-blue-500">120</p>
                <p class="text-gray-500 mt-1">Nhân viên</p>
            </div>
            <div class="bg-white rounded-2xl shadow p-6 text-center">
                <p class="text-4xl font-bold text-green-500">8</p>
                <p class="text-gray-500 mt-1">Phòng ban</p>
            </div>
            <div class="bg-white rounded-2xl shadow p-6 text-center">
                <p class="text-4xl font-bold text-orange-500">5</p>
                <p class="text-gray-500 mt-1">Đang nghỉ phép</p>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow mt-8 p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Danh sách nhân viên</h3>
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-600">
                        <th class="py-3 px-4 rounded-l-lg">Họ tên</th>
                        <th class="py-3 px-4">Phòng ban</th>
                        <th class="py-3 px-4">Chức vụ</th>
                        <th class="py-3 px-4 rounded-r-lg">Trạng thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 font-medium">Nguyễn Văn A</td>
                        <td class="py-3 px-4">Kỹ thuật</td>
                        <td class="py-3 px-4">Lập trình viên</td>
                        <td class="py-3 px-4"><span class="bg-green-100 text-green-600 px-2 py-1 rounded-full text-xs">Đang làm</span></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 font-medium">Trần Thị B</td>
                        <td class="py-3 px-4">Kế toán</td>
                        <td class="py-3 px-4">Kế toán trưởng</td>
                        <td class="py-3 px-4"><span class="bg-green-100 text-green-600 px-2 py-1 rounded-full text-xs">Đang làm</span></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 font-medium">Lê Văn C</td>
                        <td class="py-3 px-4">Nhân sự</td>
                        <td class="py-3 px-4">Trưởng phòng</td>
                        <td class="py-3 px-4"><span class="bg-orange-100 text-orange-600 px-2 py-1 rounded-full text-xs">Nghỉ phép</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>