<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDocument;
use App\Support\LeaveDocumentRules;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LeaveRequestDocumentService
{
    /** @var list<string> */
    public const ALLOWED_MIMES = ['pdf', 'jpg', 'jpeg', 'png'];

    public const MAX_KB = 5120;

    /**
     * @return array<string, mixed>
     */
    public function validationRules(string $leaveType): array
    {
        if (! LeaveDocumentRules::requiresDocument($leaveType)) {
            return [];
        }

        return [
            'supporting_document' => 'required|file|mimes:'.implode(',', self::ALLOWED_MIMES).'|max:'.self::MAX_KB,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationMessages(): array
    {
        return [
            'supporting_document.required' => 'Vui lòng đính kèm giấy tờ minh chứng cho loại nghỉ này.',
            'supporting_document.file' => 'Tệp đính kèm không hợp lệ.',
            'supporting_document.mimes' => 'Giấy tờ minh chứng: chỉ chấp nhận PDF, JPG hoặc PNG.',
            'supporting_document.max' => 'Giấy tờ minh chứng: tối đa 5MB.',
        ];
    }

    public function store(LeaveRequest $leaveRequest, Employee $employee, UploadedFile $file): LeaveRequestDocument
    {
        $directory = 'leave-requests/'.$employee->id.'/'.$leaveRequest->id;
        $storedPath = $file->store($directory, 'public');

        return LeaveRequestDocument::query()->create([
            'leave_request_id' => $leaveRequest->id,
            'file_path' => $storedPath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize() ?: 0,
        ]);
    }

    public function userCanDownload(LeaveRequestDocument $document, ?int $userId, ?string $role): bool
    {
        if (! $userId) {
            return false;
        }

        if (in_array($role, ['admin', 'accountant'], true)) {
            return true;
        }

        $document->loadMissing('leaveRequest.employee');
        $employee = $document->leaveRequest?->employee;

        if ($employee && (int) $employee->user_id === $userId) {
            return true;
        }

        if ($role === 'manager' && $employee) {
            $manager = Employee::query()->where('user_id', $userId)->first();

            return $manager && $employee->isManagedBy($manager);
        }

        return false;
    }

    public function downloadResponse(LeaveRequestDocument $document)
    {
        abort_unless(Storage::disk('public')->exists($document->file_path), 404);

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }
}
