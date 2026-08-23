<?php

namespace App\Services;

use App\Models\PayrollPeriod;
use App\Models\PayrollPeriodBankDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollPeriodBankDocumentService
{
    /** @var list<string> */
    public const ALLOWED_MIMES = ['pdf', 'xls', 'xlsx', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

    public const MAX_KB = 10240;

    /**
     * @return array<string, string>
     */
    public function validationRules(): array
    {
        return [
            'file' => 'required|file|mimes:'.implode(',', self::ALLOWED_MIMES).'|max:'.self::MAX_KB,
            'note' => 'nullable|string|max:255',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationMessages(): array
    {
        return [
            'file.required' => 'Vui lòng chọn file ngân hàng đã đóng dấu.',
            'file.file' => 'File tải lên không hợp lệ.',
            'file.mimes' => 'Chỉ chấp nhận PDF, Excel, Word hoặc ảnh (JPG, PNG).',
            'file.max' => 'File tối đa 10MB.',
            'note.max' => 'Ghi chú tối đa 255 ký tự.',
        ];
    }

    public function store(PayrollPeriod $payrollPeriod, UploadedFile $file, ?string $note, ?int $uploadedBy): PayrollPeriodBankDocument
    {
        $path = $file->store(
            'payroll-period-bank-docs/'.$payrollPeriod->id,
            PayrollPeriodBankDocument::DISK
        );

        return PayrollPeriodBankDocument::create([
            'payroll_period_id' => $payrollPeriod->id,
            'uploaded_by' => $uploadedBy,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize() ?: 0,
            'note' => $note,
        ]);
    }

    public function download(PayrollPeriodBankDocument $document): StreamedResponse
    {
        abort_unless($document->existsOnDisk(), 404, 'File không còn trên hệ thống.');

        return Storage::disk(PayrollPeriodBankDocument::DISK)->download(
            $document->file_path,
            $document->original_name
        );
    }

    public function delete(PayrollPeriodBankDocument $document): void
    {
        if ($document->existsOnDisk()) {
            Storage::disk(PayrollPeriodBankDocument::DISK)->delete($document->file_path);
        }

        $document->delete();
    }
}
