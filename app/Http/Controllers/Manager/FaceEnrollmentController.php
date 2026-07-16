<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Services\FaceEnrollmentService;
use App\Support\ManagerDepartmentResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;

class FaceEnrollmentController extends Controller
{
    public function __construct(
        private readonly FaceEnrollmentService $enrollmentService,
    ) {}

    public function index(Request $request): View
    {
        $department = ManagerDepartmentResolver::managedDepartment(Auth::user());
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', '');

        $employees = Employee::query()
            ->where('status', 'active')
            ->when($department, fn ($query) => $query->where('department_id', $department->id))
            ->when(! $department, fn ($query) => $query->whereRaw('1 = 0'))
            ->withCount('faceDescriptors')
            ->with(['department:id,department_name'])
            ->when($search, fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            }))
            ->when($status === 'enrolled', fn ($query) => $query->having('face_descriptors_count', '>', 0))
            ->when($status === 'missing', fn ($query) => $query->having('face_descriptors_count', '=', 0))
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        $baseQuery = Employee::query()
            ->where('status', 'active')
            ->when($department, fn ($query) => $query->where('department_id', $department->id))
            ->when(! $department, fn ($query) => $query->whereRaw('1 = 0'));

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'enrolled' => (clone $baseQuery)->whereHas('faceDescriptors')->count(),
        ];
        $stats['missing'] = $stats['total'] - $stats['enrolled'];

        return view('manager.face-enrollments.index', compact('employees', 'search', 'status', 'stats', 'department'));
    }

    public function store(Request $request, Employee $employee): JsonResponse
    {
        $this->ensureEmployeeInManagedDepartment($employee);

        if ($employee->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Nhân viên không còn hoạt động.',
            ], 422);
        }

        $data = $request->validate([
            'samples' => ['required', 'array', 'min:3', 'max:8'],
            'samples.*' => ['required', 'string'],
        ], [
            'samples.required' => 'Chưa có mẫu khuôn mặt.',
            'samples.min' => 'Cần chụp ít nhất 3 mẫu khuôn mặt.',
            'samples.max' => 'Tối đa 8 mẫu khuôn mặt mỗi lần đăng ký.',
        ]);

        try {
            $result = $this->enrollmentService->enroll($employee, $data['samples']);
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Đã đăng ký khuôn mặt cho {$employee->full_name}.",
            'descriptor_id' => $result['descriptor_id'],
            'sample_count' => $result['sample_count'],
        ]);
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->ensureEmployeeInManagedDepartment($employee);

        $descriptors = $employee->faceDescriptors()->get();

        foreach ($descriptors as $descriptor) {
            if ($descriptor->image_path) {
                Storage::disk('public')->delete($descriptor->image_path);
            }
        }

        $employee->faceDescriptors()->delete();

        return redirect()
            ->route('manager.face-enrollments.index')
            ->with('success', "Đã xoá dữ liệu khuôn mặt của {$employee->full_name}.");
    }

    private function ensureEmployeeInManagedDepartment(Employee $employee): Department
    {
        $department = ManagerDepartmentResolver::managedDepartment(Auth::user());

        abort_unless($department, 403, 'Tài khoản manager chưa được gắn phòng ban quản lý.');

        abort_unless(
            (int) $employee->department_id === (int) $department->id,
            403,
            'Bạn chỉ được đăng ký khuôn mặt cho nhân viên thuộc phòng ban mình quản lý.'
        );

        return $department;
    }
}
