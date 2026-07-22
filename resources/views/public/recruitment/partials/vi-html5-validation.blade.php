@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-vi-html5-validation]');
            if (!form) {
                return;
            }

            const messages = @json(__('recruitment.validation_html5'));

            const resolveMessage = (input) => {
                if (input.validity.valueMissing) {
                    return messages[input.name] || messages.invalid;
                }

                if (input.name === 'email' && input.validity.typeMismatch) {
                    return messages.email_invalid;
                }

                return messages.invalid;
            };

            form.querySelectorAll('input, select, textarea').forEach((input) => {
                input.addEventListener('invalid', (event) => {
                    event.preventDefault();
                    input.setCustomValidity(resolveMessage(input));
                    input.reportValidity();
                });

                const clearCustomValidity = () => input.setCustomValidity('');
                input.addEventListener('input', clearCustomValidity);
                input.addEventListener('change', clearCustomValidity);
            });
        });
    </script>
@endpush
