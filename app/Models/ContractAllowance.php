<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractAllowance extends Model
{
    protected $fillable = [
        'contract_id',
        'allowance_type_id',
        'amount',
        'allowance_name',
        'allowance_code',
        'calculation_type',
        'calculation_note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function allowanceType(): BelongsTo
    {
        return $this->belongsTo(AllowanceType::class);
    }
}
