@once
    @push('scripts')
        <script>
            document.querySelectorAll('form[data-interview-evaluation]').forEach((form) => {
                const statusSelect = form.querySelector('[name="status"]');
                const evaluationPanel = form.querySelector('[data-interview-evaluation-panel]');
                const noShowHint = form.querySelector('[data-interview-no-show-hint]');
                const scheduledHint = form.querySelector('[data-interview-scheduled-hint]');
                const submitButton = form.querySelector('[data-interview-submit]');
                const resultSelect = form.querySelector('[name="result"]');
                const recommendationSelect = form.querySelector('[name="recommendation"]');
                const scoreSection = form.querySelector('[data-interview-score-section]');
                const resultRow = form.querySelector('[data-interview-result-row]');
                const recommendationRow = form.querySelector('[data-interview-recommendation-row]');
                const noteSection = form.querySelector('[data-interview-note-section]');

                const recommendationOptions = {
                    passed: [
                        { value: 'hire', label: 'Nên tuyển' },
                        { value: 'consider', label: 'Cần cân nhắc' },
                    ],
                    failed: [
                        { value: 'reject', label: 'Từ chối' },
                    ],
                };

                const setSelectOptions = (select, options, selectedValue) => {
                    if (!select) {
                        return;
                    }

                    select.innerHTML = '';

                    options.forEach(({ value, label }) => {
                        const option = document.createElement('option');
                        option.value = value;
                        option.textContent = label;
                        option.selected = value === selectedValue;
                        select.appendChild(option);
                    });
                };

                const syncEvaluationVisibility = () => {
                    const status = statusSelect?.value ?? '';
                    const skipsEvaluation = status === 'no_show';
                    const isScheduled = status === 'scheduled';
                    const isCompleted = status === 'completed';

                    if (evaluationPanel) {
                        evaluationPanel.classList.toggle('hidden', skipsEvaluation);
                    }

                    if (noShowHint) {
                        noShowHint.classList.toggle('hidden', !skipsEvaluation);
                    }

                    if (scheduledHint) {
                        scheduledHint.classList.toggle('hidden', !isScheduled);
                    }

                    if (resultRow) {
                        resultRow.classList.toggle('hidden', skipsEvaluation);
                    }

                    if (resultSelect) {
                        if (isScheduled) {
                            resultSelect.innerHTML = '<option value="pending" selected>Chờ kết quả</option>';
                            resultSelect.value = 'pending';
                            resultSelect.disabled = true;
                        } else if (isCompleted) {
                            resultSelect.disabled = false;
                            const current = resultSelect.value === 'passed' || resultSelect.value === 'failed'
                                ? resultSelect.value
                                : 'passed';
                            resultSelect.innerHTML = ''
                                + '<option value="passed">Đạt</option>'
                                + '<option value="failed">Không đạt</option>';
                            resultSelect.value = current;
                        }
                    }

                    if (recommendationRow && scoreSection) {
                        const showDetails = isCompleted;
                        recommendationRow.classList.toggle('hidden', !showDetails);
                        scoreSection.classList.toggle('hidden', !showDetails);
                    }

                    if (noteSection) {
                        noteSection.classList.toggle('hidden', !isCompleted);
                    }

                    if (recommendationSelect && isCompleted && resultSelect) {
                        const result = resultSelect.value;
                        const currentRecommendation = recommendationSelect.value;

                        if (result === 'passed') {
                            const selected = ['hire', 'consider'].includes(currentRecommendation)
                                ? currentRecommendation
                                : 'hire';
                            setSelectOptions(recommendationSelect, recommendationOptions.passed, selected);
                            recommendationSelect.disabled = false;
                        } else if (result === 'failed') {
                            setSelectOptions(recommendationSelect, recommendationOptions.failed, 'reject');
                            recommendationSelect.disabled = true;
                        }
                    }

                    if (submitButton) {
                        if (skipsEvaluation) {
                            submitButton.textContent = 'Lưu trạng thái';
                        } else if (isScheduled) {
                            submitButton.textContent = 'Lưu trạng thái';
                        } else {
                            submitButton.textContent = 'Gửi kết quả cho Admin';
                        }
                    }
                };

                statusSelect?.addEventListener('change', syncEvaluationVisibility);
                resultSelect?.addEventListener('change', syncEvaluationVisibility);
                syncEvaluationVisibility();

                form.addEventListener('submit', (event) => {
                    const status = statusSelect?.value ?? '';

                    if (resultSelect?.disabled) {
                        resultSelect.disabled = false;
                    }

                    if (recommendationSelect?.disabled) {
                        recommendationSelect.disabled = false;
                    }

                    if (status === 'no_show' || status === 'scheduled') {
                        return;
                    }

                    if (status !== 'completed') {
                        return;
                    }

                    const scoreFields = @json(\App\Models\Interview::EVALUATION_SCORE_FIELDS);

                    for (const fieldName of scoreFields) {
                        const input = form.querySelector(`[name="${fieldName}"]`);
                        if (!input || input.value === '' || input.value === null) {
                            event.preventDefault();
                            input?.focus();
                            window.alert('Vui lòng nhập đủ điểm cho tất cả tiêu chí đánh giá trước khi gửi kết quả.');
                            return;
                        }
                    }

                    if (resultSelect?.value === 'passed' && !recommendationSelect?.value) {
                        event.preventDefault();
                        recommendationSelect?.focus();
                        window.alert('Vui lòng chọn đề xuất tuyển dụng.');
                    }
                });
            });
        </script>
    @endpush
@endonce
