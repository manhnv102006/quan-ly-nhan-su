<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Position;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class EmployeeController extends Controller
{
    private const DOCUMENT_RULES = [
        'documents' => ['nullable', 'array'],
        'documents.*.document_name' => ['nullable', 'string', 'max:255'],
        'documents.*.document_type' => ['nullable', 'in:cccd,cv,certificate,degree,contract'],
        'documents.*.file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        'remove_documents' => ['nullable', 'array'],
        'remove_documents.*' => ['integer', 'exists:employee_documents,id'],
    ];

    public function create(): View
    {
        $departments = Department::query()->orderBy('department_name')->get(['id', 'department_name']);
        $positions = Position::query()->orderBy('position_name')->get(['id', 'position_name']);
        $users = \App\Models\User::orderBy('name')->get();

        return view('admin.employees.create', compact('departments', 'positions', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(array_merge([
            'employee_code' => ['required', 'string', 'max:20', 'unique:employees,employee_code'],
            'full_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'in:male,female,other'],
            'date_of_birth' => ['required', 'date'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:100', 'unique:employees,email'],
            'address' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'hire_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive,resigned'],
            'user_id' => ['nullable', 'exists:users,id'],
        ], self::DOCUMENT_RULES));

        $validated['employee_code'] = strtoupper($validated['employee_code']);

        $employee = Employee::create(collect($validated)->except(['documents', 'remove_documents'])->all());

        $this->storeUploadedDocuments($employee, $request);

        return redirect()->route('admin.employees.show', $employee)->with('success', 'Thêm nhân viên thành công.');
    }

    public function show(Employee $employee): View
    {
        $employee->load(['department', 'position', 'user.role']);

        $contracts = $employee->contracts()
            ->with('contractType')
            ->latest()
            ->limit(5)
            ->get();

        $attendances = $employee->attendances()
            ->with('shift')
            ->latest('attendance_date')
            ->limit(5)
            ->get();

        $employeeKpis = $employee->employeeKpis()
            ->with('kpi')
            ->latest()
            ->limit(5)
            ->get();

        $payrolls = $employee->payrolls()
            ->with('payrollPeriod')
            ->latest()
            ->limit(5)
            ->get();

        $documents = $employee->documents()
            ->latest()
            ->get();

        $departments = Department::query()
            ->where('status', 'active')
            ->orderBy('department_name')
            ->get(['id', 'department_name']);

        $transferHistory = $employee->departmentTransfers()
            ->with(['fromDepartment', 'toDepartment', 'transferredBy'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.employees.show', compact(
            'employee',
            'contracts',
            'attendances',
            'employeeKpis',
            'payrolls',
            'documents',
            'departments',
            'transferHistory',
        ));
    }

    public function edit(Employee $employee): View
    {
        $departments = Department::query()->orderBy('department_name')->get(['id', 'department_name']);
        $positions = Position::query()->orderBy('position_name')->get(['id', 'position_name']);
        $documents = $employee->documents()->latest()->get();
        $users = \App\Models\User::orderBy('name')->get();

        return view('admin.employees.edit', compact('employee', 'departments', 'positions', 'documents', 'users'));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate(array_merge([
            'employee_code' => ['required', 'string', 'max:20', 'unique:employees,employee_code,'.$employee->id],
            'full_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'in:male,female,other'],
            'date_of_birth' => ['required', 'date'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:100', 'unique:employees,email,'.$employee->id],
            'address' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'hire_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive,resigned'],
            'user_id' => ['nullable', 'exists:users,id'],
        ], self::DOCUMENT_RULES));

        $validated['employee_code'] = strtoupper($validated['employee_code']);

        $employee->update(collect($validated)->except(['documents', 'remove_documents'])->all());

        $this->removeDocuments($employee, $request->input('remove_documents', []));
        $this->storeUploadedDocuments($employee, $request);

        return redirect()->route('admin.employees.show', $employee)->with('success', 'Cập nhật nhân viên thành công.');
    }

    public function transferDepartment(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'to_department_id' => ['required', 'exists:departments,id'],
            'effective_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'to_department_id.required' => 'Vui lòng chọn phòng ban đích.',
            'to_department_id.exists' => 'Phòng ban không hợp lệ.',
            'effective_date.required' => 'Vui lòng chọn ngày hiệu lực.',
            'effective_date.date' => 'Ngày hiệu lực không hợp lệ.',
        ]);

        $toDepartment = Department::query()
            ->where('id', $validated['to_department_id'])
            ->where('status', 'active')
            ->first();

        if (! $toDepartment) {
            return redirect()
                ->route('admin.employees.show', $employee)
                ->with('error', 'Phòng ban đích không tồn tại hoặc không hoạt động.');
        }

        if ((int) $employee->department_id === (int) $validated['to_department_id']) {
            return redirect()
                ->route('admin.employees.show', $employee)
                ->with('error', 'Phòng ban đích phải khác phòng ban hiện tại.');
        }

        $fromDepartmentName = $employee->department?->department_name ?? 'Chưa gán';

        $employee->departmentTransfers()->create([
            'from_department_id' => $employee->department_id,
            'to_department_id' => $validated['to_department_id'],
            'transferred_by' => $request->user()->id,
            'effective_date' => $validated['effective_date'],
            'note' => $validated['note'] ?? null,
        ]);

        Department::where('manager_id', $employee->id)->update(['manager_id' => null]);

        $employee->update(['department_id' => $validated['to_department_id']]);

        return redirect()
            ->route('admin.employees.show', $employee)
            ->with('success', "Đã điều chuyển nhân viên từ {$fromDepartmentName} sang {$toDepartment->department_name}.");
    }

    public function downloadDocument(Employee $employee, EmployeeDocument $document): StreamedResponse|RedirectResponse
    {
        if ($document->employee_id !== $employee->id) {
            abort(404);
        }

        $absolutePath = $document->absolutePath();

        if ($absolutePath === null) {
            return redirect()
                ->route('admin.employees.show', $employee)
                ->with('error', 'Không tìm thấy file tài liệu trên hệ thống.');
        }

        $path = ltrim($document->file_path, '/');

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path, $document->downloadFileName());
        }

        return response()->download($absolutePath, $document->downloadFileName());
    }

    public function downloadAllDocuments(Employee $employee): BinaryFileResponse|RedirectResponse
    {
        $documents = $employee->documents()
            ->get()
            ->filter(fn (EmployeeDocument $document) => $document->existsOnDisk());

        if ($documents->isEmpty()) {
            return redirect()
                ->route('admin.employees.show', $employee)
                ->with('error', 'Không có tài liệu nào để tải xuống.');
        }

        $zipDirectory = storage_path('app/temp');
        if (! is_dir($zipDirectory)) {
            mkdir($zipDirectory, 0755, true);
        }

        $tempZipPath = $zipDirectory.'/'.uniqid('employee-docs-', true).'.zip';
        $zip = new ZipArchive;

        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()
                ->route('admin.employees.show', $employee)
                ->with('error', 'Không thể tạo file nén tài liệu.');
        }

        $usedNames = [];

        foreach ($documents as $document) {
            $entryName = $document->downloadFileName();

            if (isset($usedNames[$entryName])) {
                $usedNames[$entryName]++;
                $baseName = pathinfo($entryName, PATHINFO_FILENAME);
                $extension = pathinfo($entryName, PATHINFO_EXTENSION);
                $entryName = $extension !== ''
                    ? "{$baseName}-{$usedNames[$entryName]}.{$extension}"
                    : "{$baseName}-{$usedNames[$entryName]}";
            } else {
                $usedNames[$entryName] = 1;
            }

            $zip->addFile($document->absolutePath(), $entryName);
        }

        $zip->close();

        $zipFileName = Str::slug($employee->employee_code.'-'.$employee->full_name).'-ho-so.zip';

        return response()->download($tempZipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        try {
            Department::where('manager_id', $employee->id)->update(['manager_id' => null]);

            $employee->documents()->get()->each(fn (EmployeeDocument $document) => $document->deleteFile());

            $employee->delete();
        } catch (QueryException) {
            return redirect()
                ->back()
                ->with('error', 'Không thể xóa nhân viên vì còn dữ liệu liên quan trong hệ thống.');
        }

        return redirect()
            ->route('admin.employees')
            ->with('success', 'Đã xóa nhân viên thành công.');
    }

    private function storeUploadedDocuments(Employee $employee, Request $request): void
    {
        $documents = $request->input('documents', []);

        foreach ($documents as $index => $document) {
            $file = $request->file("documents.{$index}.file");

            if (! $file instanceof UploadedFile) {
                continue;
            }

            $documentName = trim($document['document_name'] ?? '');
            $documentType = $document['document_type'] ?? 'cv';

            if ($documentName === '') {
                $documentName = match ($documentType) {
                    'cccd' => 'CCCD/CMND',
                    'cv' => 'CV',
                    'certificate' => 'Chứng chỉ',
                    'degree' => 'Bằng cấp',
                    'contract' => 'Hợp đồng',
                    default => 'Tài liệu',
                };
            }

            $filePath = $file->store('employee-documents', 'public');

            $employee->documents()->create([
                'document_name' => $documentName,
                'document_type' => $documentType,
                'file_path' => $filePath,
            ]);
        }
    }

    private function removeDocuments(Employee $employee, array $documentIds): void
    {
        if ($documentIds === []) {
            return;
        }

        $employee->documents()
            ->whereIn('id', $documentIds)
            ->get()
            ->each(function (EmployeeDocument $document) {
                $document->deleteFile();
                $document->delete();
            });
    }
}
