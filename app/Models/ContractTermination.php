<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractTermination extends Model
{
    protected $fillable = [
        'contract_id',
        'end_date',
        'note',
        'file_path',
    ];

    protected $casts = [
        'end_date' => 'date',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
