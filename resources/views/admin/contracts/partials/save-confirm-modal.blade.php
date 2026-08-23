@php
    $confirmTitle = $confirmTitle ?? 'Xác nhận lưu hợp đồng';
    $confirmAction = $confirmAction ?? 'Lưu hợp đồng';
    $confirmMessage = $confirmMessage ?? 'Kiểm tra lại thông tin trước khi lưu. Sau khi lưu, hợp đồng sẽ được ghi nhận trong hệ thống.';
@endphp

<div data-contract-confirm-modal class="fixed inset-0 z-50 hidden">
    <div data-contract-confirm-backdrop class="absolute inset-0 bg-slate-900/40"></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-md rounded-2xl border border-slate-100 bg-white p-5 shadow-xl sm:p-6">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-violet-600">Hợp đồng</p>
            <h3 class="mt-2 text-lg font-bold text-slate-800">{{ $confirmTitle }}</h3>
            <p class="mt-2 text-sm text-slate-600">{{ $confirmMessage }}</p>
            <dl class="mt-4 space-y-2 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Nhân viên</dt>
                    <dd data-confirm-employee class="font-semibold text-slate-800">—</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Loại HĐ</dt>
                    <dd data-confirm-type class="font-semibold text-slate-800">—</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Thời hạn</dt>
                    <dd data-confirm-period class="font-semibold text-slate-800">—</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Lương cơ bản</dt>
                    <dd data-confirm-salary class="font-semibold text-violet-700">—</dd>
                </div>
            </dl>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" data-contract-confirm-cancel class="admin-btn-secondary">Quay lại</button>
                <button type="button" data-contract-confirm-ok class="admin-btn-violet">{{ $confirmAction }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('[data-contract-confirm-form]');
            const modal = document.querySelector('[data-contract-confirm-modal]');
            if (!form || !modal) return;

            const backdrop = modal.querySelector('[data-contract-confirm-backdrop]');
            const cancelBtn = modal.querySelector('[data-contract-confirm-cancel]');
            const okBtn = modal.querySelector('[data-contract-confirm-ok]');
            let confirmed = false;

            function textOf(selector) {
                const el = form.querySelector(selector);
                if (!el) return '—';
                if (el.tagName === 'SELECT') {
                    return el.selectedOptions[0]?.text?.trim() || '—';
                }
                return (el.value || '').trim() || '—';
            }

            function formatDate(value) {
                if (!value || value === '—') return '—';
                const parts = value.split('-');
                if (parts.length !== 3) return value;
                return parts[2] + '/' + parts[1] + '/' + parts[0];
            }

            function fillSummary() {
                const employeeDisplay = form.querySelector('[data-employee-select]')
                    ? textOf('[data-employee-select]')
                    : (form.querySelector('input.admin-field[disabled]')?.value || '—');

                modal.querySelector('[data-confirm-employee]').textContent = employeeDisplay;
                modal.querySelector('[data-confirm-type]').textContent = textOf('#contract_type_id');

                const start = formatDate(form.querySelector('#start_date')?.value);
                const end = formatDate(form.querySelector('#end_date')?.value);
                modal.querySelector('[data-confirm-period]').textContent = end === '—' ? start + ' → Không xác định' : start + ' → ' + end;

                const salary = form.querySelector('#salary')?.value || '';
                modal.querySelector('[data-confirm-salary]').textContent = salary ? salary + ' ₫' : '—';
            }

            function openModal() {
                fillSummary();
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeModal() {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            form.addEventListener('submit', function (event) {
                if (confirmed) return;
                event.preventDefault();
                openModal();
            });

            okBtn.addEventListener('click', function () {
                confirmed = true;
                closeModal();
                form.requestSubmit();
            });

            cancelBtn.addEventListener('click', closeModal);
            backdrop.addEventListener('click', closeModal);
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        });
    </script>
@endpush
