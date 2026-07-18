<?php

namespace App\Http\Requests\Manager;

use App\Models\Employee;
use App\Services\ManagerScopeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()?->isManager() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'leader_employee_id' => [
                'nullable',
                'integer',
                'exists:employees,id',
                function ($attribute, $value, $fail) {
                    if (! $value) {
                        return;
                    }
                    $manager = app(ManagerScopeService::class)->resolveManagerEmployee(Auth::user());
                    $leader = Employee::query()->with('user.role')->find($value);

                    if (! $manager || ! $leader || ! app(ManagerScopeService::class)->managesEmployee($manager, $leader)) {
                        $fail('Trưởng nhóm không thuộc phòng ban của bạn.');

                        return;
                    }

                    if (! $leader->user?->isLeader()) {
                        $fail('Nhân viên được chọn phải có vai trò Trưởng nhóm trong phòng ban.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên nhóm.',
        ];
    }
}
