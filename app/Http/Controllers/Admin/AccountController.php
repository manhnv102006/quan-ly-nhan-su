<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Services\ManagerDepartmentSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'role_id' => (string) $request->input('role_id', ''),
            'status' => (string) $request->input('status', ''),
            'verified' => (string) $request->input('verified', ''),
        ];

        $users = User::query()
            ->with('role')
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['role_id'] !== '', fn ($q) => $q->where('role_id', $filters['role_id']))
            ->when($filters['status'] !== '', fn ($q) => $q->where('status', $filters['status']))
            ->when($filters['verified'] === 'yes', fn ($q) => $q->whereNotNull('email_verified_at'))
            ->when($filters['verified'] === 'no', fn ($q) => $q->whereNull('email_verified_at'))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'trashed' => User::onlyTrashed()->count(),
        ];

        $roles = Role::query()->orderBy('id')->get();

        return view('admin.accounts.index', compact('users', 'stats', 'filters', 'roles'));
    }

    public function create(): View
    {
        $roles = Role::query()->orderBy('id')->get();

        return view('admin.accounts.create', compact('roles'));
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        User::create([
            'username' => $validated['username'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'status' => $validated['status'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.accounts')
            ->with('success', 'Thêm tài khoản thành công.');
    }

    public function show(User $user): View
    {
        $user->load(['role', 'employee.department', 'employee.position']);

        $availableEmployees = Employee::query()
            ->withoutLinkedAccount()
            ->orderBy('full_name')
            ->get(['id', 'employee_code', 'full_name', 'email']);

        return view('admin.accounts.show', compact('user', 'availableEmployees'));
    }

    public function edit(User $user): View
    {
        $roles = Role::query()->orderBy('id')->get();

        return view('admin.accounts.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username,'.$user->id],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'email_verified' => ['nullable', 'boolean'],
        ], [
            'username.required' => 'Tên đăng nhập là bắt buộc',
            'username.alpha_dash' => 'Tên đăng nhập chỉ được chứa chữ, số, gạch ngang và gạch dưới',
            'username.unique' => 'Tên đăng nhập đã tồn tại',
            'name.required' => 'Họ tên là bắt buộc',
            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Email không hợp lệ',
            'email.unique' => 'Email đã được sử dụng',
            'role_id.required' => 'Vai trò là bắt buộc',
            'role_id.exists' => 'Vai trò không hợp lệ',
            'status.required' => 'Trạng thái là bắt buộc',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
        ]);

        if ($user->id === auth()->id() && $validated['status'] === 'inactive') {
            return back()
                ->withInput()
                ->withErrors(['status' => 'Bạn không thể vô hiệu hóa tài khoản đang đăng nhập.']);
        }

        $data = [
            'username' => $validated['username'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'status' => $validated['status'],
        ];

        $emailChanged = $validated['email'] !== $user->email;
        $markVerified = $request->boolean('email_verified');

        if ($markVerified) {
            $data['email_verified_at'] = ($emailChanged || ! $user->email_verified_at) ? now() : $user->email_verified_at;
        } else {
            $data['email_verified_at'] = null;
        }

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.accounts')
            ->with('success', 'Cập nhật tài khoản thành công.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->back()
                ->with('error', 'Bạn không thể khóa/mở tài khoản đang đăng nhập.');
        }

        $locking = $user->status === 'active';

        $user->update([
            'status' => $locking ? 'inactive' : 'active',
        ]);

        $message = $locking
            ? 'Đã khóa tài khoản thành công.'
            : 'Đã mở khóa tài khoản thành công.';

        return redirect()
            ->back()
            ->with('success', $message);
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'password.required' => 'Mật khẩu mới là bắt buộc',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($validator)
                ->with('open_reset_modal', $user->id)
                ->with('reset_username', $user->username);
        }

        $user->update([
            'password' => Hash::make($validator->validated()['password']),
        ]);

        return redirect()
            ->back()
            ->with('success', "Đã đặt lại mật khẩu cho tài khoản {$user->username} thành công.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($error = $this->deleteGuardMessage($user)) {
            return redirect()->back()->with('error', $error);
        }

        $this->releaseUniqueFields($user);

        Employee::query()
            ->where('user_id', $user->id)
            ->update(['user_id' => null]);

        $user->delete();

        return redirect()
            ->route('admin.accounts')
            ->with('success', "Đã chuyển tài khoản {$user->username} vào thùng rác.");
    }

    public function trash(Request $request): View
    {
        $users = User::onlyTrashed()
            ->with('role')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.accounts.trash', compact('users'));
    }

    public function restore(int $id): RedirectResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);

        if ($error = $this->restoreConflictMessage($user)) {
            return redirect()->back()->with('error', $error);
        }

        $this->restoreUniqueFields($user);
        $user->restore();

        return redirect()
            ->route('admin.accounts.trash')
            ->with('success', "Đã khôi phục tài khoản {$user->username}.");
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);

        if ($error = $this->deleteGuardMessage($user)) {
            return redirect()->back()->with('error', $error);
        }

        $username = $user->username;

        Employee::query()
            ->where('user_id', $user->id)
            ->update(['user_id' => null]);

        $user->forceDelete();

        return redirect()
            ->route('admin.accounts.trash')
            ->with('success', "Đã xóa vĩnh viễn tài khoản {$username}.");
    }

    private function deleteGuardMessage(User $user): ?string
    {
        if ($user->id === auth()->id()) {
            return 'Bạn không thể xóa tài khoản đang đăng nhập.';
        }

        if ($user->isAdmin()) {
            $otherAdmins = User::withTrashed()
                ->where('id', '!=', $user->id)
                ->whereHas('role', fn ($query) => $query->where('name', Role::ADMIN))
                ->count();

            if ($otherAdmins === 0) {
                return 'Không thể xóa tài khoản quản trị viên cuối cùng.';
            }
        }

        return null;
    }

    public function linkEmployee(Request $request, User $user): RedirectResponse
    {
        if ($user->employee && $user->employee->hasLinkedAccount()) {
            return redirect()
                ->route('admin.accounts.show', $user)
                ->with('error', 'Tài khoản này đã được liên kết với một nhân viên.');
        }

        $validated = $request->validate([
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('employees', 'id'),
            ],
        ], [
            'employee_id.required' => 'Vui lòng chọn nhân viên để liên kết.',
            'employee_id.exists' => 'Nhân viên không tồn tại.',
        ]);

        $employee = Employee::query()->findOrFail($validated['employee_id']);

        if (! Employee::query()->withoutLinkedAccount()->whereKey($employee->id)->exists()) {
            return redirect()
                ->route('admin.accounts.show', $user)
                ->with('error', 'Nhân viên này đã được liên kết với tài khoản khác.');
        }

        $employee->update(['user_id' => $user->id]);

        app(ManagerDepartmentSyncService::class)->syncAfterEmployeeSaved($employee->fresh());

        return redirect()
            ->route('admin.accounts.show', $user)
            ->with('success', "Đã liên kết tài khoản {$user->username} với nhân viên {$employee->full_name}.");
    }

    public function unlinkEmployee(User $user): RedirectResponse
    {
        $employee = $user->employee;

        if (! $employee) {
            return redirect()
                ->route('admin.accounts.show', $user)
                ->with('error', 'Tài khoản này chưa liên kết nhân viên nào.');
        }

        $employee->update(['user_id' => null]);

        return redirect()
            ->route('admin.accounts.show', $user)
            ->with('success', "Đã gỡ liên kết nhân viên {$employee->full_name} khỏi tài khoản {$user->username}.");
    }

    private function releaseUniqueFields(User $user): void
    {
        $usernameSuffix = '::d'.$user->id;

        $user->update([
            'email' => $user->email.'::deleted::'.$user->id,
            'username' => Str::limit($user->username, 50 - strlen($usernameSuffix), '').$usernameSuffix,
        ]);
    }

    private function restoreUniqueFields(User $user): void
    {
        if (str_contains($user->email, '::deleted::')) {
            $user->email = explode('::deleted::', $user->email, 2)[0];
        }

        if (preg_match('/::d\d+$/', $user->username)) {
            $user->username = preg_replace('/::d\d+$/', '', $user->username);
        }

        $user->save();
    }

    private function restoreConflictMessage(User $user): ?string
    {
        $originalEmail = str_contains($user->email, '::deleted::')
            ? explode('::deleted::', $user->email, 2)[0]
            : $user->email;

        $originalUsername = preg_match('/::d\d+$/', $user->username)
            ? preg_replace('/::d\d+$/', '', $user->username)
            : $user->username;

        if (User::where('email', $originalEmail)->where('id', '!=', $user->id)->exists()) {
            return 'Không thể khôi phục vì email đã được sử dụng bởi tài khoản khác.';
        }

        if (User::where('username', $originalUsername)->where('id', '!=', $user->id)->exists()) {
            return 'Không thể khôi phục vì tên đăng nhập đã được sử dụng bởi tài khoản khác.';
        }

        return null;
    }
}
