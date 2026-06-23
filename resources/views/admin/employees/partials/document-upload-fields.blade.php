<div class="md:col-span-2 border-t border-slate-100 pt-6 mt-2">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-base font-semibold text-slate-800">Tài liệu hồ sơ</h3>
            <p class="text-sm text-slate-500 mt-1">Tải lên file PDF, Word hoặc hình ảnh (tối đa 10MB/file)</p>
        </div>
        <button type="button"
                id="add-document-row"
                class="inline-flex items-center gap-1 px-4 py-2 rounded-xl bg-violet-100 text-violet-700 text-sm font-medium hover:bg-violet-200 transition">
            + Thêm tài liệu
        </button>
    </div>

    <div id="document-rows" class="space-y-4">
        <div class="document-row grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tên tài liệu</label>
                <input type="text" name="documents[0][document_name]" value="{{ old('documents.0.document_name') }}"
                       placeholder="VD: CCCD, CV..."
                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Loại tài liệu</label>
                <select name="documents[0][document_type]"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                    <option value="cccd" @selected(old('documents.0.document_type') === 'cccd')>CCCD/CMND</option>
                    <option value="cv" @selected(old('documents.0.document_type', 'cv') === 'cv')>CV</option>
                    <option value="certificate" @selected(old('documents.0.document_type') === 'certificate')>Chứng chỉ</option>
                    <option value="degree" @selected(old('documents.0.document_type') === 'degree')>Bằng cấp</option>
                    <option value="contract" @selected(old('documents.0.document_type') === 'contract')>Hợp đồng</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">File tài liệu</label>
                <input type="file" name="documents[0][file]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-slate-800 text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-violet-100 file:text-violet-700 file:text-sm file:font-medium">
            </div>
        </div>
    </div>

    @error('documents.*.document_name') <p class="text-red-600 text-xs mt-2">{{ $message }}</p> @enderror
    @error('documents.*.document_type') <p class="text-red-600 text-xs mt-2">{{ $message }}</p> @enderror
    @error('documents.*.file') <p class="text-red-600 text-xs mt-2">{{ $message }}</p> @enderror
</div>

<script>
    (function () {
        const container = document.getElementById('document-rows');
        const addBtn = document.getElementById('add-document-row');
        if (!container || !addBtn) return;

        let rowIndex = container.querySelectorAll('.document-row').length;

        addBtn.addEventListener('click', function () {
            const firstRow = container.querySelector('.document-row');
            if (!firstRow) return;

            const clone = firstRow.cloneNode(true);
            clone.querySelectorAll('input, select').forEach(function (field) {
                const name = field.getAttribute('name');
                if (name) {
                    field.setAttribute('name', name.replace(/\[\d+\]/, '[' + rowIndex + ']'));
                }
                if (field.type === 'file') {
                    field.value = '';
                } else {
                    field.value = field.tagName === 'SELECT' ? 'cv' : '';
                }
            });

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'mt-2 text-sm text-red-600 hover:text-red-700 font-medium';
            removeBtn.textContent = 'Xóa dòng này';
            removeBtn.addEventListener('click', function () {
                clone.remove();
            });

            const wrapper = document.createElement('div');
            wrapper.appendChild(clone);
            wrapper.appendChild(removeBtn);
            container.appendChild(wrapper);
            rowIndex++;
        });
    })();
</script>
