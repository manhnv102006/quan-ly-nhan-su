<div id="link-account-modal"
     class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm"
     style="display: none;">
    <div class="bg-white rounded-3xl shadow-xl w-full max-w-lg mx-4">
        <div class="px-6 py-5 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-800">Liên kết tài khoản</h3>
            <p class="text-sm text-slate-500 mt-1">{{ $employee->full_name }} · {{ $employee->employee_code }}</p>
        </div>

        <form action="{{ route('admin.employees.link-account', $employee) }}" method="POST" class="p-6 space-y-5">
            @csrf
            @method('PATCH')

            <div class="rounded-xl bg-blue-50 border border-blue-100 px-4 py-3 text-sm text-blue-700">
                Mỗi nhân viên chỉ được liên kết với <strong>một tài khoản</strong> duy nhất.
            </div>

            <div>
                <label for="user_id" class="block text-sm font-medium text-slate-700 mb-2">
                    Chọn tài khoản <span class="text-red-500">*</span>
                </label>

                @if ($availableAccounts->isEmpty())
                    <p class="text-sm text-amber-600 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                        Không còn tài khoản nào chưa liên kết. Hãy tạo tài khoản mới hoặc gỡ liên kết tài khoản khác trước.
                    </p>
                @else
                    <select name="user_id" id="user_id" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                        <option value="">-- Chọn tài khoản --</option>
                        @foreach ($availableAccounts as $account)
                            <option value="{{ $account->id }}" @selected(old('user_id') == $account->id)>
                                {{ $account->username }} · {{ $account->name }}
                                @if ($account->role)
                                    ({{ $account->role->label() }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                @endif

                @error('user_id')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" id="close-link-account-modal"
                        class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                    Hủy
                </button>
                <button type="submit"
                        @disabled($availableAccounts->isEmpty())
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
        const modal = document.getElementById('link-account-modal');
        const openBtn = document.getElementById('open-link-account-modal');
        const closeBtn = document.getElementById('close-link-account-modal');

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

        @if (request('open') === 'link-account' || $errors->has('user_id'))
            openModal();
        @endif
    })();
</script>
