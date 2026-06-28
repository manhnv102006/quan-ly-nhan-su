<x-admin-layout title="Giao KPI cho Manager">

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.kpi-assignments.index') }}" class="flex items-center justify-center w-10 h-10 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                ←
            </a>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Giao KPI cho Manager</h2>
                <p class="text-sm text-slate-500 mt-1">Tạo bản ghi giao KPI mới</p>
            </div>
        </div>

        {{-- Form --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">

            <div class="px-8 py-6 border-b border-slate-100">
                <h3 class="font-semibold text-slate-800">Thông tin giao KPI</h3>
            </div>

            <form action="{{ route('admin.kpi-assignments.store') }}" method="POST" class="p-8 space-y-6">

                @csrf

                {{-- KPI Selection --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label for="kpi_id" class="block text-sm font-semibold text-slate-800 mb-2">
                            KPI <span class="text-red-500">*</span>
                        </label>
                        <select id="kpi_id" name="kpi_id" required class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                            <option value="">-- Chọn KPI --</option>
                            @foreach($kpis as $kpi)
                                <option value="{{ $kpi->id }}" {{ old('kpi_id') == $kpi->id ? 'selected' : '' }}>
                                    {{ $kpi->code }} - {{ $kpi->title }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('kpi_id')" class="mt-2" />
                    </div>

                    {{-- Manager Selection --}}
                    <div>
                        <label for="manager_id" class="block text-sm font-semibold text-slate-800 mb-2">
                            Manager <span class="text-red-500">*</span>
                        </label>
                        <select id="manager_id" name="manager_id" required class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                            <option value="">-- Chọn Manager --</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }} ({{ $manager->email }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('manager_id')" class="mt-2" />
                    </div>

                </div>

                {{-- Target --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label for="target" class="block text-sm font-semibold text-slate-800 mb-2">
                            Target (%) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="target" name="target" step="0.01" min="0" required
                            value="{{ old('target') }}"
                            placeholder="Nhập target"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                        <x-input-error :messages="$errors->get('target')" class="mt-2" />
                    </div>

                </div>

                {{-- Dates --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label for="start_date" class="block text-sm font-semibold text-slate-800 mb-2">
                            Ngày bắt đầu <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="start_date" name="start_date" required
                            value="{{ old('start_date') }}"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-semibold text-slate-800 mb-2">
                            Ngày kết thúc <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="end_date" name="end_date" required
                            value="{{ old('end_date') }}"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                        <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                    </div>

                </div>

                {{-- Note --}}
                <div>
                    <label for="note" class="block text-sm font-semibold text-slate-800 mb-2">
                        Ghi chú
                    </label>
                    <textarea id="note" name="note" rows="4" placeholder="Nhập ghi chú..."
                        class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent">{{ old('note') }}</textarea>
                    <x-input-error :messages="$errors->get('note')" class="mt-2" />
                </div>

                {{-- Actions --}}
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="px-6 py-3 bg-violet-600 text-white font-medium rounded-lg hover:bg-violet-700 transition shadow-lg shadow-violet-500/20">
                        Giao KPI
                    </button>
                    <a href="{{ route('admin.kpi-assignments.index') }}" class="px-6 py-3 bg-slate-200 text-slate-700 font-medium rounded-lg hover:bg-slate-300 transition">
                        Hủy
                    </a>
                </div>

            </form>

        </div>

    </div>

</x-admin-layout>
