<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractActivityLog extends Model
{
    public const ACTION_EXTEND = 'extend';

    public const ACTION_CONVERT = 'convert';

    public const ACTION_TERMINATE = 'terminate';

    public const ACTION_CREATE = 'create';

    protected $fillable = [
        'contract_id',
        'related_contract_id',
        'action',
        'description',
        'performed_by',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function relatedContract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'related_contract_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
