<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TaxDependent;
use App\Models\TaxDependentDocument;
use App\Support\TaxDependentDocumentRules;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TaxDependentDocumentService
{
    /** @var list<string> */
    public const ALLOWED_MIMES = ['pdf', 'jpg', 'jpeg', 'png'];

    public const MAX_KB = 5120;

    /**
     * @return array<string, mixed>
     */
    public function validationRules(string $relationship, ?string $childCategory): array
    {
        $rules = [
            'child_category' => 'required_if:relationship,child|nullable|in:minor,student',
            'date_of_birth' => 'required_if:relationship,child|nullable|date',
        ];

        foreach (TaxDependentDocumentRules::requiredTypes($relationship, $childCategory) as $type) {
            $rules['document_'.$type] = 'required|file|mimes:'.implode(',', self::ALLOWED_MIMES).'|max:'.self::MAX_KB;
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function validationMessages(): array
    {
        $messages = [
            'child_category.required_if' => 'Vui lòng chọn loại con (dưới 18 tuổi hoặc đang học đại học).',
            'date_of_birth.required_if' => 'Vui lòng nhập ngày sinh của con.',
        ];

        foreach (TaxDependentDocumentRules::TYPE_LABELS as $type => $label) {
            $messages['document_'.$type.'.required'] = "Vui lòng đính kèm: {$label}.";
            $messages['document_'.$type.'.mimes'] = "{$label}: chỉ chấp nhận PDF, JPG hoặc PNG.";
            $messages['document_'.$type.'.max'] = "{$label}: tối đa 5MB.";
        }

        return $messages;
    }

    public function assertChildAgeMatchesCategory(?string $dateOfBirth, ?string $childCategory, ?string $startDate): void
    {
        if (! $dateOfBirth || ! $childCategory) {
            return;
        }

        $ref = $startDate ? Carbon::parse($startDate) : now();
        $age = Carbon::parse($dateOfBirth)->diffInYears($ref);

        if ($childCategory === TaxDependentDocumentRules::childMinor() && $age >= 18) {
            throw ValidationException::withMessages([
                'date_of_birth' => 'Con dưới 18 tuổi: ngày sinh phải tương ứng tuổi dưới 18 tại ngày bắt đầu giảm trừ.',
            ]);
        }

        if ($childCategory === TaxDependentDocumentRules::childStudent() && $age < 18) {
            throw ValidationException::withMessages([
                'date_of_birth' => 'Con đang học đại học: ngày sinh phải tương ứng từ 18 tuổi trở lên.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $validatedRequest  includes document_* UploadedFile keys
     */
    public function attachFromRequest(TaxDependent $dependent, Employee $employee, array $validatedRequest): void
    {
        $types = TaxDependentDocumentRules::requiredTypes(
            $dependent->relationship,
            $dependent->child_category,
        );

        foreach ($types as $type) {
            $key = 'document_'.$type;
            $file = $validatedRequest[$key] ?? null;
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $this->storeOne($dependent, $employee, $type, $file);
        }
    }

    public function storeOne(TaxDependent $dependent, Employee $employee, string $type, UploadedFile $file): TaxDependentDocument
    {
        $directory = 'tax-dependents/'.$employee->id.'/'.$dependent->id;
        $stored = $file->store($directory, 'public');

        return TaxDependentDocument::query()->updateOrCreate(
            [
                'tax_dependent_id' => $dependent->id,
                'document_type' => $type,
            ],
            [
                'file_path' => $stored,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize() ?: 0,
            ]
        );
    }

    public function userCanDownload(TaxDependentDocument $document, ?int $userId, ?string $role): bool
    {
        if (! $userId) {
            return false;
        }

        if (in_array($role, ['admin', 'accountant'], true)) {
            return true;
        }

        $dependent = $document->taxDependent;
        if (! $dependent) {
            return false;
        }

        $employee = $dependent->employee;

        return $employee && (int) $employee->user_id === $userId;
    }

    public function downloadResponse(TaxDependentDocument $document)
    {
        abort_unless(Storage::disk('public')->exists($document->file_path), 404);

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }
}
