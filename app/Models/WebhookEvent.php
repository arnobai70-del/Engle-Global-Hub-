<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A provider webhook event, stored once per provider event ID (idempotency).
 */
class WebhookEvent extends Model
{
    /** @var list<string> */
    protected $fillable = ['provider', 'event_id', 'event_type', 'payload_hash', 'processed_at'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['processed_at' => 'datetime'];
    }
}
