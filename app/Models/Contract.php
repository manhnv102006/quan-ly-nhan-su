<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'signed_date' => 'date',
        ];
    }

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class);
    }
}
