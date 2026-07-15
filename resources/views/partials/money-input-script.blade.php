@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const moneyInputs = document.querySelectorAll('.money-input');

    function formatMoney(value) {
        const digits = (value || '').toString().replace(/\D/g, '');
        if (digits === '') return '';
        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    moneyInputs.forEach(function (input) {
        if (input.value) {
            input.value = formatMoney(input.value);
        }

        input.addEventListener('input', function () {
            this.value = formatMoney(this.value);
        });

        const form = input.closest('form');
        if (form && !form.dataset.moneyInputBound) {
            form.dataset.moneyInputBound = '1';
            form.addEventListener('submit', function () {
                form.querySelectorAll('.money-input').forEach(function (field) {
                    field.value = (field.value || '').replace(/\D/g, '');
                });
            });
        }
    });
});
</script>
@endpush
