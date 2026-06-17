<x-admin-layout title="Chi tiết phòng ban">

    <div class="space-y-6">
    
        {{-- Header --}}
        <div class="flex items-center justify-between">
    
            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    Chi tiết phòng ban
                </h2>
    
                <p class="text-slate-500 mt-1">
                    Xem thông tin chi tiết của phòng ban
                </p>
            </div>
    
            <a href="{{ route('admin.departments') }}"
               class="px-5 py-3 rounded-xl bg-slate-200 text-slate-700 font-medium hover:bg-slate-300 transition">
                ← Quay lại
            </a>
    
        </div>
    
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
            {{-- Thông tin --}}
            <div class="lg:col-span-2">
    
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
    
                    <div class="px-6 py-5 border-b border-slate-100">
    
                        <h3 class="text-lg font-semibold text-slate-800">
                            Thông tin phòng ban
                        </h3>
    
                    </div>
    
                    <div class="p-6">
    
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">
                                    Mã phòng ban
                                </label>
    
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $department->department_code }}
                                </div>
                            </div>
    
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">
                                    Tên phòng ban
                                </label>
    
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $department->department_name }}
                                </div>
                            </div>
    
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">
                                    Quản lý
                                </label>
    
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $department->manager_id ?? 'Chưa chỉ định' }}
                                </div>
                            </div>
    
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">
                                    Ngày tạo
                                </label>
    
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $department->created_at?->format('d/m/Y H:i') }}
                                </div>
                            </div>
    
                        </div>
    
                        <div class="mt-5">
    
                            <label class="block text-sm font-medium text-slate-500 mb-2">
                                Mô tả
                            </label>
    
                            <div class="min-h-[140px] px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700">
                                {{ $department->description ?: 'Không có mô tả' }}
                            </div>
    
                        </div>
    
                    </div>
    
                </div>
    
            </div>
    
            {{-- Card bên phải --}}
            <div>
    
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 text-center">
    
                    <div class="w-24 h-24 mx-auto rounded-3xl bg-violet-100 flex items-center justify-center">
    
                        <svg class="w-12 h-12 text-violet-600"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
    
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M3 21h18M5 21V7l7-4 7 4v14" />
    
                        </svg>
    
                    </div>
    
                    <h3 class="mt-5 text-xl font-bold text-slate-800">
                        {{ $department->department_name }}
                    </h3>
    
                    <p class="text-slate-500 mt-2">
                        {{ $department->department_code }}
                    </p>
    
                    <div class="mt-4">
    
                        <span class="inline-flex items-center px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 text-sm font-semibold">
                            Active
                        </span>
    
                    </div>
    
                </div>
    
            </div>
    
        </div>
    
    </div>
    
    </x-admin-layout>