<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with('role')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
        ];

        return view('admin.accounts.index', compact('users', 'stats'));
    }

    public function create(): View
    {
        $roles = Role::query()->orderBy('id')->get();

        return view('admin.accounts.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
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
            'password.required' => 'Mật khẩu là bắt buộc',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
        ]);

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

        return view('admin.accounts.show', compact('user'));
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
}
