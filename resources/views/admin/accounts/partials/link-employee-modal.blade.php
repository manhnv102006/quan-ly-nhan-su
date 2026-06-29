<div id="link-employee-modal"
     class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm"
     style="display: none;">
    <div class="bg-white rounded-3xl shadow-xl w-full max-w-lg mx-4">
        <div class="px-6 py-5 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-800">Liên kết hồ sơ nhân viên</h3>
            <p class="text-sm text-slate-500 mt-1">{{ $user->username }} · {{ $user->name }}</p>
        </div>

        <form action="{{ route('admin.accounts.link-employee', $user) }}" method="POST" class="p-6 space-y-5">
            @csrf
            @method('PATCH')

            <div class="rounded-xl bg-blue-50 border border-blue-100 px-4 py-3 text-sm text-blue-700">
                Mỗi tài khoản chỉ được liên kết với <strong>một nhân viên</strong> duy nhất.
            </div>

            <div>
                <label for="employee_id" class="block text-sm font-medium text-slate-700 mb-2">
                    Chọn nhân viên <span class="text-red-500">*</span>
                </label>

                @if ($availableEmployees->isEmpty())
                    <p class="text-sm text-amber-600 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                        Không còn nhân viên nào chưa liên kết tài khoản.
                    </p>
                @else
                    <select name="employee_id" id="employee_id" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                        <option value="">-- Chọn nhân viên --</option>
                        @foreach ($availableEmployees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>
                                {{ $employee->employee_code }} · {{ $employee->full_name }} ({{ $employee->email }})
                            </option>
                        @endforeach
                    </select>
                @endif

                @error('employee_id')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" id="close-link-employee-modal"
                        class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                    Hủy
                </button>
                <button type="submit"
                        @disabled($availableEmployees->isEmpty())
                        class="flex-1 px-5 py-3 rounded-xl text-white font-medium transition disabled:opacity-50 disabled:cursor-not-allowed"
                        style="background-color: #7c3aed;">
                    Liên kết
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('link-employee-modal');
        const openBtn = document.getElementById('open-link-employee-modal');
        const closeBtn = document.getElementById('close-link-employee-modal');

        if (!modal || !openBtn) return;

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.style.display = 'flex';
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.style.display = 'none';
        }

        openBtn.addEventListener('click', openModal);
        closeBtn?.addEventListener('click', closeModal);

        modal.addEventListener('click', function (event) {
            if (event.target === modal) closeModal();
        });

        @if (request('open') === 'link-employee' || $errors->has('employee_id'))
            openModal();
        @endif
    })();
</script>
