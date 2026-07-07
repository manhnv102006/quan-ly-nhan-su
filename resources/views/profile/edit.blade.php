@if ($layout === 'admin')
    <x-admin-layout title="Hồ sơ cá nhân">
        @include('profile.partials.content')
    </x-admin-layout>
@elseif ($layout === 'manager')
    <x-manager-layout
        title="Hồ sơ cá nhân"
        subtitle="Quản lý thông tin tài khoản và bảo mật đăng nhập."
    >
        @include('profile.partials.content')
    </x-manager-layout>
@else
    <x-employee-layout
        title="Hồ sơ cá nhân"
        subtitle="Cập nhật thông tin cá nhân và mật khẩu của bạn."
    >
        @include('profile.partials.content')
    </x-employee-layout>
@endif
