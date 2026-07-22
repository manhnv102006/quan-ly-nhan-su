@once
    @push('scripts')
        <script>
            document.querySelectorAll('form[data-interview-evaluation]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    const status = form.querySelector('[name="status"]')?.value ?? '';
                    const result = form.querySelector('[name="result"]')?.value ?? '';
                    const requiresScores =
                        status === 'completed' || result === 'passed' || result === 'failed';

                    if (!requiresScores) {
                        return;
                    }

                    const scoreFields = @json(\App\Models\Interview::EVALUATION_SCORE_FIELDS);

                    for (const fieldName of scoreFields) {
                        const input = form.querySelector(`[name="${fieldName}"]`);
                        if (!input || input.value === '' || input.value === null) {
                            event.preventDefault();
                            input?.focus();
                            window.alert(
                                'Vui lòng nhập đủ điểm cho tất cả tiêu chí đánh giá trước khi hoàn thành.'
                            );
                            return;
                        }
                    }
                });
            });
        </script>
    @endpush
@endonce
