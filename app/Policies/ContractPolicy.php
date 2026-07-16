<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;
use App\Services\ManagerScopeService;
use Illuminate\Auth\Access\Response;

class ContractPolicy
{
    public function __construct(private readonly ManagerScopeService $managerScope)
    {
    }

    public function viewAny(User $user): Response
    {
        if ($user->isAdmin() || $user->isManager() || $user->isEmployee() || $user->isLeader()) {
            return Response::allow();
        }

        return Response::deny('Bạn không có quyền xem hợp đồng.', 403);
    }

    public function view(User $user, Contract $contract): Response
    {
        if ($user->isAdmin()) {
            return Response::allow();
        }

        $contract->loadMissing('employee');

        if ($user->isEmployee() || $user->isLeader()) {
            if ($contract->employee?->user_id === $user->id) {
                return Response::allow();
            }

            return Response::deny('Bạn chỉ được xem hợp đồng của chính mình.', 403);
        }

        if ($user->isManager()) {
            return $this->managerCanViewResponse($user, $contract);
        }

        return Response::deny('Bạn không có quyền xem hợp đồng này.', 403);
    }

    public function create(User $user): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Chỉ Admin được tạo hợp đồng.', 403);
    }

    public function update(User $user, Contract $contract): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Chỉ Admin được sửa hợp đồng.', 403);
    }

    public function delete(User $user, Contract $contract): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Chỉ Admin được xóa hợp đồng.', 403);
    }

    public function download(User $user, Contract $contract): Response
    {
        return $this->view($user, $contract);
    }

    private function managerCanViewResponse(User $user, Contract $contract): Response
    {
        $employee = $contract->employee;

        if (! $employee) {
            return Response::deny('Hợp đồng không gắn nhân viên.', 403);
        }

        $manager = $this->managerScope->resolveManagerEmployee($user);

        if ($manager && $this->managerScope->managesEmployee($manager, $employee)) {
            return Response::allow();
        }

        return Response::deny('Bạn chỉ được xem hợp đồng nhân viên phòng mình.', 403);
    }
}
