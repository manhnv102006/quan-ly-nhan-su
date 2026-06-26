<x-admin-layout title="Ứng viên">
    @php
        $filters = $filters ?? [
            'search' => '',
            'status' => '',
            'job_post_id' => '',
            'cv_status' => '',
            'converted' => '',
            'created_from' => '',
            'created_to' => '',
        ];
        $statusLabels = [
            'new' => 'Mới',
            'interview' => 'Phỏng vấn',
            'passed' => 'Đạt',
            'failed' => 'Không đạt',
        ];
        $statusClasses = [
            'new' => 'bg-sky-100 text-sky-700',
            'interview' => 'bg-amber-100 text-amber-700',
            'passed' => 'bg-emerald-100 text-emerald-700',
            'failed' => 'bg-rose-100 text-rose-700',
        ];
        $statsCards = [
            ['label' => 'Tổng', 'value' => $stats['total'] ?? 0, 'class' => 'text-slate-900 bg-slate-50'],
            ['label' => 'Mới', 'value' => $stats['new'] ?? 0, 'class' => 'text-sky-700 bg-sky-50'],
            ['label' => 'Phỏng vấn', 'value' => $stats['interview'] ?? 0, 'class' => 'text-amber-700 bg-amber-50'],
            ['label' => 'Đạt', 'value' => $stats['passed'] ?? 0, 'class' => 'text-emerald-700 bg-emerald-50'],
            ['label' => 'Không đạt', 'value' => $stats['failed'] ?? 0, 'class' => 'text-rose-700 bg-rose-50'],
            ['label' => 'Đã nhận việc', 'value' => $stats['converted'] ?? 0, 'class' => 'text-indigo-700 bg-indigo-50'],
        ];
    @endphp

    @include('admin.recruitment.partials.ui-contrast')

    <div class="recruitment-ui max-w-full overflow-hidden space-y-6">
        <section class="recruitment-hero rounded-[2rem] border border-white/80 bg-white/90 p-5 shadow-sm shadow-slate-200/60 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                        <a href="{{ route('admin.recruitment') }}" class="hover:text-cyan-600">Tuyển dụng</a>
                        <span>/</span>
                        <span class="font-semibold text-slate-700">Ứng viên</span>
                    </div>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Quản lý ứng viên</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Lọc hồ sơ, theo dõi trạng thái phỏng vấn, CV và tình trạng chuyển thành nhân viên.
                    </p>
                </div>

                <a href="{{ route('admin.recruitment.candidates.create') }}"
                   class="recruitment-btn-primary inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700">
                    Thêm ứng viên
                </a>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700">{{ session('error') }}</div>
        @endif

        <section class="recruitment-stats grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
            @foreach ($statsCards as $card)
                <div class="rounded-[1.5rem] border border-white/80 {{ $card['class'] }} p-4 shadow-sm">
                    <p class="truncate text-xs font-bold uppercase tracking-wide opacity-80">{{ $card['label'] }}</p>
                    <p class="mt-2 text-2xl font-black">{{ $card['value'] }}</p>
                </div>
            @endforeach
        </section>

        <section class="recruitment-panel rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm shadow-slate-200/60">
            <form method="GET" action="{{ route('admin.recruitment.candidates') }}" class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Tìm kiếm</label>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Tên, email, số điện thoại, vị trí..."
                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                </div>

                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Trạng thái</label>
                    <select name="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                        <option value="">Tất cả</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Tin tuyển dụng</label>
                    <select name="job_post_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                        <option value="">Tất cả</option>
                        @foreach ($jobPosts as $jobPost)
                            <option value="{{ $jobPost->id }}" @selected((string) $filters['job_post_id'] === (string) $jobPost->id)>{{ $jobPost->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Tình trạng</label>
                    <div class="grid grid-cols-2 gap-3">
                        <select name="cv_status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                            <option value="">CV</option>
                            <option value="has_cv" @selected($filters['cv_status'] === 'has_cv')>Có CV</option>
                            <option value="missing_cv" @selected($filters['cv_status'] === 'missing_cv')>Thiếu CV</option>
                        </select>
                        <select name="converted" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                            <option value="">Nhân viên</option>
                            <option value="yes" @selected($filters['converted'] === 'yes')>Đã chuyển</option>
                            <option value="no" @selected($filters['converted'] === 'no')>Chưa chuyển</option>
                        </select>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Từ ngày</label>
                    <input type="date" name="created_from" value="{{ $filters['created_from'] }}"
                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                </div>

                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Đến ngày</label>
                    <input type="date" name="created_to" value="{{ $filters['created_to'] }}"
                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                </div>

                <div class="flex flex-col gap-3 lg:col-span-8 lg:flex-row lg:items-end">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">Lọc ứng viên</button>
                    <a href="{{ route('admin.recruitment.candidates') }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-100 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-200">Xóa lọc</a>
                </div>
            </form>
        </section>

        <section class="recruitment-panel overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
            <div class="overflow-x-auto">
                <table class="min-w-[980px] divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Ứng viên</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Tin tuyển dụng</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Liên hệ</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">CV</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Trạng thái</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Nhân viên</th>
                            <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wide text-slate-500">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($candidates as $candidate)
                            <tr class="align-top transition hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <p class="break-words font-bold text-slate-900">{{ $candidate->full_name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $candidate->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    <p class="max-w-xs break-words">{{ $candidate->jobPost?->title ?? 'Chưa gắn tin' }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    <p class="break-words">{{ $candidate->phone }}</p>
                                    <p class="mt-1 break-words">{{ $candidate->email }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($candidate->cv_file)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Có CV</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">Thiếu CV</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses[$candidate->status] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ $statusLabels[$candidate->status] ?? $candidate->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm">
                                    @if ($candidate->employee)
                                        <a href="{{ route('admin.employees.show', $candidate->employee) }}" class="font-bold text-cyan-700 hover:text-cyan-800">
                                            {{ $candidate->employee->employee_code }}
                                        </a>
                                    @else
                                        <span class="text-slate-400">Chưa chuyển</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <a href="{{ route('admin.recruitment.candidates.show', $candidate) }}" class="rounded-xl bg-cyan-50 px-3 py-2 text-sm font-bold text-cyan-700 transition hover:bg-cyan-100">Xem hồ sơ</a>
                                        <a href="{{ route('admin.recruitment.candidates.edit', $candidate) }}" class="rounded-xl bg-amber-50 px-3 py-2 text-sm font-bold text-amber-700 transition hover:bg-amber-100">Cập nhật</a>
                                        @if ($candidate->status !== 'passed')
                                            <form action="{{ route('admin.recruitment.candidates.update', $candidate) }}" method="POST" onsubmit="return confirm('Chấp nhận ứng viên này?')">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="job_post_id" value="{{ $candidate->job_post_id }}">
                                                <input type="hidden" name="full_name" value="{{ $candidate->full_name }}">
                                                <input type="hidden" name="phone" value="{{ $candidate->phone }}">
                                                <input type="hidden" name="email" value="{{ $candidate->email }}">
                                                <input type="hidden" name="address" value="{{ $candidate->address }}">
                                                <input type="hidden" name="birth_date" value="{{ $candidate->birth_date?->format('Y-m-d') }}">
                                                <input type="hidden" name="status" value="passed">
                                                <button type="submit" class="rounded-xl bg-emerald-50 px-3 py-2 text-sm font-bold text-emerald-700 transition hover:bg-emerald-100">Chấp nhận</button>
                                            </form>
                                        @endif
                                        @if ($candidate->status !== 'failed')
                                            <form action="{{ route('admin.recruitment.candidates.update', $candidate) }}" method="POST" onsubmit="return confirm('Từ chối ứng viên này?')">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="job_post_id" value="{{ $candidate->job_post_id }}">
                                                <input type="hidden" name="full_name" value="{{ $candidate->full_name }}">
                                                <input type="hidden" name="phone" value="{{ $candidate->phone }}">
                                                <input type="hidden" name="email" value="{{ $candidate->email }}">
                                                <input type="hidden" name="address" value="{{ $candidate->address }}">
                                                <input type="hidden" name="birth_date" value="{{ $candidate->birth_date?->format('Y-m-d') }}">
                                                <input type="hidden" name="status" value="failed">
                                                <button type="submit" class="rounded-xl bg-rose-50 px-3 py-2 text-sm font-bold text-rose-700 transition hover:bg-rose-100">Từ chối</button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.recruitment.candidates.destroy', $candidate) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa ứng viên này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl bg-red-50 px-3 py-2 text-sm font-bold text-red-700 transition hover:bg-red-100">Xóa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-sm">
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700">
                                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128A12.318 12.318 0 0 1 8.624 21a12.318 12.318 0 0 1-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.965-3.07M12 7.875a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z" />
                                            </svg>
                                        </div>
                                        <h3 class="mt-4 text-base font-black text-slate-900">Chưa có ứng viên nào</h3>
                                        <p class="mt-2 text-sm leading-6 text-slate-500">Hãy thêm ứng viên mới hoặc thay đổi bộ lọc để xem thêm hồ sơ phù hợp.</p>
                                        <a href="{{ route('admin.recruitment.candidates.create') }}" class="mt-4 inline-flex rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-cyan-700">Thêm ứng viên</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $candidates->links() }}
            </div>
        </section>
    </div>
</x-admin-layout>
