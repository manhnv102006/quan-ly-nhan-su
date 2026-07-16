<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractExtension extends Model
{
    protected $fillable = [
        'contract_id',
        'new_contract_id',
        'old_end_date',
        'new_end_date',
        'note',
        'performed_by',
    ];

    protected $casts = [
        'old_end_date' => 'date',
        'new_end_date' => 'date',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function newContract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'new_contract_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
