<div id="tai-lieu-ho-so" class="md:col-span-2 rounded-3xl border border-violet-100 bg-gradient-to-br from-violet-50/80 via-white to-indigo-50/50 p-6 mt-2">
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-600 flex items-center justify-center shadow-md shadow-violet-500/20 shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-800">Tài liệu hồ sơ</h3>
                <p class="text-sm text-slate-500 mt-1">Tải lên file PDF, Word hoặc hình ảnh (tối đa 10MB/file)</p>
            </div>
        </div>
        <button type="button"
                id="add-document-row"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition shadow-sm shadow-violet-500/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Thêm tài liệu
        </button>
    </div>

    <div id="document-rows" class="space-y-4">
        <div class="document-row grid grid-cols-1 md:grid-cols-3 gap-4 p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tên tài liệu</label>
                <input type="text" name="documents[0][document_name]" value="{{ old('documents.0.document_name') }}"
                       placeholder="VD: CCCD, CV..."
                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm focus:ring-2 focus:ring-violet-500/30 focus:border-violet-400 transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Loại tài liệu</label>
                <select name="documents[0][document_type]"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm focus:ring-2 focus:ring-violet-500/30 focus:border-violet-400 transition">
                    <option value="cccd" @selected(old('documents.0.document_type') === 'cccd')>CCCD/CMND</option>
                    <option value="cv" @selected(old('documents.0.document_type', 'cv') === 'cv')>CV</option>
                    <option value="certificate" @selected(old('documents.0.document_type') === 'certificate')>Chứng chỉ</option>
                    <option value="degree" @selected(old('documents.0.document_type') === 'degree')>Bằng cấp</option>
                    <option value="contract" @selected(old('documents.0.document_type') === 'contract')>Hợp đồng</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">File tài liệu</label>
                <input type="file" name="documents[0][file]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                       class="w-full rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-slate-800 text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-violet-600 file:text-white file:text-sm file:font-medium hover:file:bg-violet-700 transition">
            </div>
        </div>
    </div>

    @error('documents.*.document_name') <p class="text-red-600 text-xs mt-3">{{ $message }}</p> @enderror
    @error('documents.*.document_type') <p class="text-red-600 text-xs mt-3">{{ $message }}</p> @enderror
    @error('documents.*.file') <p class="text-red-600 text-xs mt-3">{{ $message }}</p> @enderror
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

            const wrapper = document.createElement('div');
            wrapper.className = 'relative';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'absolute -top-2 -right-2 w-8 h-8 rounded-full bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center text-sm font-bold shadow-sm transition';
            removeBtn.innerHTML = '×';
            removeBtn.title = 'Xóa dòng này';
            removeBtn.addEventListener('click', function () {
                wrapper.remove();
            });

            wrapper.appendChild(clone);
            wrapper.appendChild(removeBtn);
            container.appendChild(wrapper);
            rowIndex++;
        });
    })();
</script>
