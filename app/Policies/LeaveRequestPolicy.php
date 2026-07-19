<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\ManagerEmployeeResolver;
use Illuminate\Auth\Access\Response;

class LeaveRequestPolicy
{
    public function __construct(private readonly ManagerEmployeeResolver $managerResolver)
    {
    }

    public function viewAny(User $user): Response
    {
        if ($user->isAdmin() || $user->isManager() || $user->isEmployee()) {
            return Response::allow();
        }

        return Response::deny('Bạn không có quyền xem danh sách đơn nghỉ phép.', 403);
    }

    public function viewAnyAsManager(User $user): Response
    {
        if (! $user->isManager()) {
            return Response::deny('Chỉ quản lý mới được truy cập chức năng duyệt nghỉ phép.', 403);
        }

        return Response::allow();
    }

    public function view(User $user, LeaveRequest $leaveRequest): Response
    {
        if ($user->isAdmin()) {
            return Response::allow();
        }

        if ($user->isEmployee()) {
            $leaveRequest->loadMissing('employee');

            if ($leaveRequest->employee?->user_id === $user->id) {
                return Response::allow();
            }

            return Response::deny('Bạn không có quyền xem đơn nghỉ phép này.', 403);
        }

        if ($user->isManager()) {
            return $this->managerCanManageResponse($user, $leaveRequest);
        }

        return Response::deny('Bạn không có quyền xem đơn nghỉ phép này.', 403);
    }

    public function viewAsManager(User $user, LeaveRequest $leaveRequest): Response
    {
        if (! $user->isManager()) {
            return Response::deny('Chỉ quản lý mới được xem đơn nghỉ phép cấp dưới.', 403);
        }

        return $this->managerCanManageResponse($user, $leaveRequest);
    }

    public function create(User $user): Response
    {
        if ($user->isAdmin() || $user->isManager() || $user->isEmployee()) {
            return Response::allow();
        }

        return Response::deny('Bạn không có quyền tạo đơn nghỉ phép.', 403);
    }

    public function approve(User $user, LeaveRequest $leaveRequest): Response
    {
        return $this->decideApprovalAccess($user, $leaveRequest, 'duyệt');
    }

    public function reject(User $user, LeaveRequest $leaveRequest): Response
    {
        return $this->decideApprovalAccess($user, $leaveRequest, 'từ chối');
    }

    protected function decideApprovalAccess(User $user, LeaveRequest $leaveRequest, string $action): Response
    {
        $leaveRequest->loadMissing('employee.user');
        $isFromManager = $leaveRequest->employee?->user?->isManager() ?? false;

        if ($isFromManager) {
            if ($user->isAdmin()) {
                return Response::allow();
            }

            return Response::deny("Đơn nghỉ phép của quản lý chỉ Admin mới được {$action}.", 403);
        }

        if ($user->isAdmin()) {
            return Response::deny('Admin chỉ được xem đơn nghỉ phép, không được '.$action.'.', 403);
        }

        if (! $user->isManager()) {
            return Response::deny('Chỉ quản lý mới được '.$action.' đơn nghỉ phép.', 403);
        }

        return $this->managerCanManageResponse($user, $leaveRequest);
    }

    protected function managerCanManageResponse(User $user, LeaveRequest $leaveRequest): Response
    {
        $leaveRequest->loadMissing('employee');

        if ($leaveRequest->employee?->user_id === $user->id) {
            return Response::deny('Bạn không thể tự duyệt đơn nghỉ phép của chính mình.', 403);
        }

        $manager = $this->managerResolver->resolve($user);
        if (! $manager) {
            return Response::deny('Tài khoản quản lý chưa liên kết hồ sơ nhân viên. Vui lòng liên hệ quản trị để được hỗ trợ.', 403);
        }

        if (! $leaveRequest->employee?->isManagedBy($manager)) {
            return Response::deny('Bạn không có quyền xử lý đơn nghỉ phép này. Đơn không thuộc nhân viên do bạn quản lý.', 403);
        }

        return Response::allow();
    }
}
