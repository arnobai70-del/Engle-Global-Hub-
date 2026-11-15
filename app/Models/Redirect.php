<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An admin-managed URL redirect (for example after a page moves).
 */
class Redirect extends Model
{
    /** @var list<string> */
    protected $fillable = ['from_path', 'to_url', 'status_code', 'is_active'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status_code' => 'integer', 'is_active' => 'boolean', 'hits' => 'integer', 'last_hit_at' => 'datetime'];
    }
}
