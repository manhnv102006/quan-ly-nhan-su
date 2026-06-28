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

    public function viewAsManager(User $user, LeaveRequest $leaveRequest): Response
    {
        return $this->managerCanManageResponse($user, $leaveRequest);
    }

    public function approve(User $user, LeaveRequest $leaveRequest): Response
    {
        return $this->managerCanManageResponse($user, $leaveRequest);
    }

    public function reject(User $user, LeaveRequest $leaveRequest): Response
    {
        return $this->managerCanManageResponse($user, $leaveRequest);
    }

    protected function managerCanManageResponse(User $user, LeaveRequest $leaveRequest): Response
    {
        if (! $user->isManager()) {
            return Response::deny('Chỉ quản lý mới được thực hiện thao tác này.', 403);
        }

        $manager = $this->managerResolver->resolve($user);
        if (! $manager) {
            return Response::deny('Tài khoản quản lý chưa liên kết hồ sơ nhân viên. Vui lòng liên hệ quản trị để được hỗ trợ.', 403);
        }

        $leaveRequest->loadMissing('employee');

        if (! $leaveRequest->employee?->isManagedBy($manager)) {
            return Response::deny('Bạn không có quyền xử lý đơn nghỉ phép này. Đơn không thuộc nhân viên do bạn quản lý.', 403);
        }

        return Response::allow();
    }
}
