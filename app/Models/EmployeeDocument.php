<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeDocument extends Model
{
    protected $table = 'employee_documents';

    protected $fillable = [
        'employee_id',
        'document_name',
        'document_type',
        'file_path',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function existsOnDisk(): bool
    {
        return $this->absolutePath() !== null;
    }

    public function absolutePath(): ?string
    {
        $path = ltrim($this->file_path, '/');

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->path($path);
        }

        $publicPath = public_path($path);
        if (is_file($publicPath)) {
            return $publicPath;
        }

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->path($path);
        }

        return null;
    }

    public function downloadFileName(): string
    {
        $extension = pathinfo($this->file_path, PATHINFO_EXTENSION);
        $safeName = Str::slug($this->document_name) ?: 'tai-lieu';

        return $extension ? "{$safeName}.{$extension}" : $this->document_name;
    }

    public function typeLabel(): string
    {
        return match ($this->document_type) {
            'cccd' => 'CCCD/CMND',
            'cv' => 'CV',
            'certificate' => 'Chứng chỉ',
            'degree' => 'Bằng cấp',
            'contract' => 'Hợp đồng',
            default => $this->document_type,
        };
    }

    public function deleteFile(): void
    {
        $path = ltrim($this->file_path, '/');

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);

            return;
        }

        $publicPath = public_path($path);
        if (is_file($publicPath)) {
            unlink($publicPath);
        }
    }
}
