<x-admin-layout title="Phong van">
    @php
        $statusLabels = [
            'scheduled' => 'Da len lich',
            'completed' => 'Da phong van',
            'cancelled' => 'Da huy',
            'no_show' => 'Khong den',
        ];
        $resultLabels = [
            'pending' => 'Cho ket qua',
            'passed' => 'Dat',
            'failed' => 'Khong dat',
        ];
        $recommendationLabels = [
            '' => 'Chua chon',
            'hire' => 'Nen tuyen',
            'consider' => 'Can can nhac',
            'reject' => 'Tu choi',
        ];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('admin.recruitment') }}" class="hover:text-cyan-600 transition">Tuyen dung</a>
                    <span>/</span>
                    <span class="font-medium text-slate-700">Phong van</span>
                </div>
                <h2 class="mt-2 text-2xl font-bold text-slate-800">Quan ly phong van</h2>
                <p class="mt-1 text-sm text-slate-500">Cap nhat trang thai buoi phong van, diem danh gia va ket qua ung vien.</p>
            </div>

            <a href="{{ route('admin.recruitment.interviews.create') }}"
               class="inline-flex items-center rounded-xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700">
                Tao lich phong van
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <p class="font-semibold">Vui long kiem tra lai thong tin phong van:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Tong lich</p>
                <p class="mt-2 text-2xl font-bold text-slate-800">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="rounded-3xl border border-amber-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-amber-600">Cho ket qua</p>
                <p class="mt-2 text-2xl font-bold text-amber-700">{{ $stats['pending'] ?? 0 }}</p>
            </div>
            <div class="rounded-3xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-emerald-600">Dat</p>
                <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $stats['passed'] ?? 0 }}</p>
            </div>
            <div class="rounded-3xl border border-rose-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-rose-600">Khong dat</p>
                <p class="mt-2 text-2xl font-bold text-rose-700">{{ $stats['failed'] ?? 0 }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-4 text-sm shadow-sm">
                <span class="font-semibold text-slate-700">Da len lich:</span> {{ $stats['scheduled'] ?? 0 }}
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 text-sm shadow-sm">
                <span class="font-semibold text-slate-700">Da phong van:</span> {{ $stats['completed'] ?? 0 }}
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 text-sm shadow-sm">
                <span class="font-semibold text-slate-700">Da huy:</span> {{ $stats['cancelled'] ?? 0 }}
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 text-sm shadow-sm">
                <span class="font-semibold text-slate-700">Khong den:</span> {{ $stats['no_show'] ?? 0 }}
            </div>
        </div>

        <div class="space-y-4">
            @forelse ($interviews as $interview)
                <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="mb-5 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800">{{ $interview->candidate?->full_name ?? 'Ung vien da xoa' }}</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $interview->candidate?->jobPost?->title ?? 'Chua gan tin tuyen dung' }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                Phong van: {{ $interview->interview_date?->format('d/m/Y H:i') ?? '-' }}
                                @if ($interview->interviewer)
                                    - {{ $interview->interviewer->full_name }}
                                @endif
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                {{ $statusLabels[$interview->status] ?? $interview->status }}
                            </span>
                            @if ($interview->result === 'passed')
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Dat</span>
                            @elseif ($interview->result === 'failed')
                                <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">Khong dat</span>
                            @else
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Cho ket qua</span>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('admin.recruitment.interviews.update', $interview) }}" method="POST" class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Trang thai buoi phong van</label>
                            <select name="status" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                                @foreach ($statusLabels as $value => $label)
                                    <option value="{{ $value }}" @selected($interview->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Ket qua</label>
                            <select name="result" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                                @foreach ($resultLabels as $value => $label)
                                    <option value="{{ $value }}" @selected($interview->result === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">De xuat</label>
                            <select name="recommendation" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                                @foreach ($recommendationLabels as $value => $label)
                                    <option value="{{ $value }}" @selected((string) $interview->recommendation === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Diem tong quan</label>
                            <input type="number" min="0" max="10" name="overall_score" value="{{ $interview->overall_score }}"
                                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Diem ky thuat</label>
                            <input type="number" min="0" max="10" name="technical_score" value="{{ $interview->technical_score }}"
                                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Diem thai do</label>
                            <input type="number" min="0" max="10" name="attitude_score" value="{{ $interview->attitude_score }}"
                                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Diem van hoa</label>
                            <input type="number" min="0" max="10" name="culture_score" value="{{ $interview->culture_score }}"
                                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                        </div>

                        <div class="lg:col-span-4">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Ghi chu chung</label>
                            <textarea name="note" rows="3" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">{{ $interview->note }}</textarea>
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Diem manh</label>
                            <textarea name="strengths" rows="3" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">{{ $interview->strengths }}</textarea>
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Diem can cai thien</label>
                            <textarea name="weaknesses" rows="3" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">{{ $interview->weaknesses }}</textarea>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 lg:col-span-4">
                            <button type="submit" class="rounded-xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700">
                                Cap nhat ket qua
                            </button>
                            @if ($interview->candidate)
                                <a href="{{ route('admin.recruitment.candidates.show', $interview->candidate) }}" class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                    Xem ung vien
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            @empty
                <div class="rounded-3xl border border-slate-100 bg-white px-5 py-12 text-center text-sm text-slate-500 shadow-sm">
                    Chua co lich phong van nao.
                </div>
            @endforelse
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
            {{ $interviews->links() }}
        </div>
    </div>
</x-admin-layout>
