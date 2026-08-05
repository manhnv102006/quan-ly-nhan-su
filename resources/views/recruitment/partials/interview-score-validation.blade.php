@once
    @push('scripts')
        <script>
            document.querySelectorAll('form[data-interview-evaluation]').forEach((form) => {
                const statusSelect = form.querySelector('[name="status"]');
                const evaluationPanel = form.querySelector('[data-interview-evaluation-panel]');
                const noShowHint = form.querySelector('[data-interview-no-show-hint]');
                const submitButton = form.querySelector('[data-interview-submit]');
                const resultSelect = form.querySelector('[name="result"]');

                const syncEvaluationVisibility = () => {
                    const skipsEvaluation = (statusSelect?.value ?? '') === 'no_show';

                    if (evaluationPanel) {
                        evaluationPanel.classList.toggle('hidden', skipsEvaluation);
                    }

                    if (noShowHint) {
                        noShowHint.classList.toggle('hidden', !skipsEvaluation);
                    }

                    if (resultSelect) {
                        if (skipsEvaluation) {
                            resultSelect.removeAttribute('required');
                        } else {
                            resultSelect.setAttribute('required', 'required');
                        }
                    }

                    if (submitButton) {
                        submitButton.textContent = skipsEvaluation ? 'Lưu trạng thái' : 'Lưu kết quả';
                    }
                };

                statusSelect?.addEventListener('change', syncEvaluationVisibility);
                syncEvaluationVisibility();

                form.addEventListener('submit', (event) => {
                    const status = statusSelect?.value ?? '';

                    if (status === 'no_show') {
                        return;
                    }

                    const result = resultSelect?.value ?? '';
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
