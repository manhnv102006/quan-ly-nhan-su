<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'contract_type_id',
        'contract_code',
        'start_date',
        'end_date',
        'salary',
        'status',
        'file_path',
        'signed_date',
        'note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'signed_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class);
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(ContractExtension::class)->orderByDesc('created_at');
    }

    public function terminations(): HasMany
    {
        return $this->hasMany(ContractTermination::class)->orderByDesc('created_at');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Đang hiệu lực',
            'expired' => 'Đã hết hạn',
            'terminated' => 'Đã thanh lý',
            default => 'Chưa xác định',
        };
    }
}
