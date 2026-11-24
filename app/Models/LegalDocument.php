<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    public const TYPES = [
        'incorporation' => 'Certificate of Incorporation (RJSC)',
        'trade_licence' => 'Trade Licence',
        'tax' => 'Tax / VAT registration',
        'agency_licence' => 'Industry-specific licence',
        'other' => 'Other official document',
    ];

    protected $fillable = [
        'title', 'document_type', 'issuer', 'reference_number', 'issued_at',
        'expires_at', 'verification_status', 'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date', 'expires_at' => 'date', 'verified_at' => 'date',
            'approved_at' => 'datetime', 'published_at' => 'datetime',
            'redaction_confirmed' => 'boolean',
        ];
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('publication_status', 'published')
            ->where('verification_status', 'verified')
            ->whereNotNull('approved_at')
            ->where(function (Builder $query): void {
                $query->whereNull('expires_at')->orWhereDate('expires_at', '>=', today());
            });
    }

    public function canPublish(): bool
    {
        return $this->verification_status === 'verified'
            && $this->verified_at !== null
            && $this->approved_at !== null
            && ($this->expires_at === null || $this->expires_at->greaterThanOrEqualTo(today()))
            && ($this->public_copy_path === null || $this->redaction_confirmed);
    }
}
