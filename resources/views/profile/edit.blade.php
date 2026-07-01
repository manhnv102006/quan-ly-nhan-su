@if ($layout === 'admin')
    <x-admin-layout title="Hồ sơ cá nhân">
        @include('profile.partials.content')
    </x-admin-layout>
@elseif ($layout === 'manager')
    <x-staff-layout
        title="Hồ sơ cá nhân"
        subtitle="Quản lý thông tin tài khoản và bảo mật đăng nhập."
        role="manager"
        :navigation="$navigation">
        @include('profile.partials.content')
    </x-staff-layout>
@else
    <x-staff-layout
        title="Hồ sơ cá nhân"
        subtitle="Cập nhật thông tin cá nhân và mật khẩu của bạn."
        role="employee"
        :navigation="$navigation">
        @include('profile.partials.content')
    </x-staff-layout>
@endif
