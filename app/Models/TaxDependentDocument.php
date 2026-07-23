<?php

namespace App\Models;

use App\Support\TaxDependentDocumentRules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TaxDependentDocument extends Model
{
    protected $fillable = [
        'tax_dependent_id',
        'document_type',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function taxDependent(): BelongsTo
    {
        return $this->belongsTo(TaxDependent::class);
    }

    public function typeLabel(): string
    {
        return TaxDependentDocumentRules::label($this->document_type);
    }

    public function deleteFile(): void
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }
    }
}
