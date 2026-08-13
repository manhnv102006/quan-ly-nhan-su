<?php

namespace App\Policies;

use App\Models\EarlyLeaveRequest;
use App\Models\User;
use App\Services\ManagerEmployeeResolver;
use Illuminate\Auth\Access\Response;

class EarlyLeaveRequestPolicy
{
    public function __construct(private readonly ManagerEmployeeResolver $managerResolver)
    {
    }

    public function view(User $user, EarlyLeaveRequest $earlyLeaveRequest): Response
    {
        if ($user->isAdmin()) {
            return Response::allow();
        }

        $earlyLeaveRequest->loadMissing('employee');

        if ($earlyLeaveRequest->employee?->user_id === $user->id) {
            return Response::allow();
        }

        if ($user->isManager()) {
            $manager = $this->managerResolver->resolve($user);

            if ($manager && $earlyLeaveRequest->employee?->isManagedBy($manager)) {
                return Response::allow();
            }
        }

        return Response::deny('Bạn không có quyền xem đơn về sớm này.', 403);
    }

    public function approve(User $user, EarlyLeaveRequest $earlyLeaveRequest): Response
    {
        return $this->decideApprovalAccess($user, $earlyLeaveRequest, 'duyệt');
    }

    public function reject(User $user, EarlyLeaveRequest $earlyLeaveRequest): Response
    {
        return $this->decideApprovalAccess($user, $earlyLeaveRequest, 'từ chối');
    }

    protected function decideApprovalAccess(User $user, EarlyLeaveRequest $earlyLeaveRequest, string $action): Response
    {
        $earlyLeaveRequest->loadMissing('employee.user');
        $requiresAdminApproval = $earlyLeaveRequest->employee?->requiresAdminApproval() ?? false;

        if ($requiresAdminApproval) {
            if ($user->isAdmin()) {
                return Response::allow();
            }

            return Response::deny("Đơn về sớm của quản lý/kế toán chỉ Admin mới được {$action}.", 403);
        }

        if ($user->isAdmin()) {
            return Response::deny('Admin chỉ được xem đơn về sớm, không được '.$action.' đơn của nhân viên.', 403);
        }

        if (! $user->isManager()) {
            return Response::deny('Chỉ quản lý mới được '.$action.' đơn về sớm.', 403);
        }

        return $this->managerCanManageResponse($user, $earlyLeaveRequest);
    }

    protected function managerCanManageResponse(User $user, EarlyLeaveRequest $earlyLeaveRequest): Response
    {
        $earlyLeaveRequest->loadMissing('employee');

        if ($earlyLeaveRequest->employee?->user_id === $user->id) {
            return Response::deny('Bạn không thể tự duyệt đơn về sớm của chính mình.', 403);
        }

        $manager = $this->managerResolver->resolve($user);
        if (! $manager) {
            return Response::deny('Tài khoản quản lý chưa liên kết hồ sơ nhân viên. Vui lòng liên hệ quản trị để được hỗ trợ.', 403);
        }

        if (! $earlyLeaveRequest->employee?->isManagedBy($manager)) {
            return Response::deny('Bạn không có quyền xử lý đơn về sớm này. Đơn không thuộc nhân viên do bạn quản lý.', 403);
        }

        return Response::allow();
    }
}
