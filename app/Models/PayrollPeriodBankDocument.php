<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PayrollPeriodBankDocument extends Model
{
    public const DISK = 'local';

    protected $fillable = [
        'payroll_period_id',
        'uploaded_by',
        'original_name',
        'file_path',
        'file_size',
        'note',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function existsOnDisk(): bool
    {
        return filled($this->file_path) && Storage::disk(self::DISK)->exists($this->file_path);
    }

    public function formattedSize(): string
    {
        $bytes = max(0, (int) $this->file_size);

        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1, ',', '.').' KB';
        }

        return number_format($bytes / 1048576, 1, ',', '.').' MB';
    }
}
