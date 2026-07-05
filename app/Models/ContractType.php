<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contract_name',
        'duration_month',
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Loại hợp đồng thực tập (nhận diện theo tên) -> không có phụ cấp.
     */
    public function isInternship(): bool
    {
        $name = mb_strtolower($this->contract_name ?? '');

        return str_contains($name, 'thực tập')
            || str_contains($name, 'thuc tap')
            || str_contains($name, 'intern');
    }
}
