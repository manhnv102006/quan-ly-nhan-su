<script>
    document.addEventListener('DOMContentLoaded', function () {
        const kpiSelect = document.getElementById('kpi_id');
        const managerSelect = document.getElementById('manager_id');
        const emptyMsg = document.getElementById('manager_empty');
        const managerHint = document.getElementById('manager_hint');
        const managerOptions = Array.from(managerSelect.options).filter(o => o.value !== '');
        const panel = document.getElementById('kpi_details_panel');
        const emptyPanel = document.getElementById('kpi_details_empty');
        const warning = document.getElementById('kpi_preview_warning');

        function parseIds(str) {
            return (str || '').split(',').map(s => s.trim()).filter(Boolean);
        }

        function formatDate(value) {
            if (!value) return '—';
            const parts = value.split('-');
            if (parts.length !== 3) return value;
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }

        function updateKpiPreview() {
            const option = kpiSelect.options[kpiSelect.selectedIndex];
            const hasKpi = Boolean(kpiSelect.value);

            panel.classList.toggle('hidden', !hasKpi);
            emptyPanel.classList.toggle('hidden', hasKpi);
            warning.classList.add('hidden');

            if (!hasKpi) {
                return;
            }

            const target = option.dataset.targetDisplay || '—';
            const startDate = option.dataset.startDate || '';
            const endDate = option.dataset.endDate || '';
            const departments = option.dataset.departmentsLabel || '—';
            const positions = option.dataset.positionsLabel || '—';
            const missing = [];

            document.getElementById('kpi_preview_target').textContent = target;
            document.getElementById('kpi_preview_dates').textContent = startDate && endDate
                ? `${formatDate(startDate)} → ${formatDate(endDate)}`
                : 'Chưa thiết lập';
            document.getElementById('kpi_preview_departments').textContent = departments;
            document.getElementById('kpi_preview_positions').textContent = positions;

            const tasksWrapper = document.getElementById('kpi_preview_tasks_wrapper');
            const tasksList = document.getElementById('kpi_preview_tasks');
            const tasks = (option.dataset.tasks || '').split('||').map(t => t.trim()).filter(Boolean);
            tasksList.innerHTML = '';
            if (tasks.length > 0) {
                tasks.forEach(function (task, i) {
                    const li = document.createElement('li');
                    li.className = 'flex gap-2';
                    li.innerHTML = '<span class="font-semibold text-violet-600">' + (i + 1) + '.</span><span>' + task.replace(/</g, '&lt;') + '</span>';
                    tasksList.appendChild(li);
                });
                tasksWrapper.classList.remove('hidden');
            } else {
                tasksWrapper.classList.add('hidden');
            }

            if (!option.dataset.targetValue) {
                missing.push('mục tiêu');
            }
            if (!startDate || !endDate) {
                missing.push('ngày bắt đầu/kết thúc');
            }

            if (missing.length > 0) {
                warning.textContent = 'KPI này chưa có ' + missing.join(' và ') + '. Vui lòng cập nhật KPI trước khi giao.';
                warning.classList.remove('hidden');
            }
        }

        function filterManagers() {
            const kpiOption = kpiSelect.options[kpiSelect.selectedIndex];
            const kpiDeptIds = kpiOption ? parseIds(kpiOption.dataset.departments) : [];

            if (managerHint) {
                managerHint.classList.add('hidden');
            }
            emptyMsg.classList.add('hidden');

            if (!kpiSelect.value) {
                managerOptions.forEach(o => o.hidden = true);
                if (managerSelect.value) managerSelect.value = '';
                if (managerHint) managerHint.classList.remove('hidden');
                return;
            }

            let visibleCount = 0;
            managerOptions.forEach(o => {
                const managerDeptIds = parseIds(o.dataset.departments);
                const match = kpiDeptIds.length === 0
                    ? true
                    : managerDeptIds.some(id => kpiDeptIds.includes(id));
                o.hidden = !match;
                if (match) visibleCount++;
            });

            const current = managerSelect.options[managerSelect.selectedIndex];
            if (managerSelect.value && current && current.hidden) {
                managerSelect.value = '';
            }

            if (visibleCount === 0) {
                emptyMsg.classList.remove('hidden');
            }
        }

        kpiSelect.addEventListener('change', function () {
            filterManagers();
            updateKpiPreview();
        });

        filterManagers();
        updateKpiPreview();
    });
</script>
