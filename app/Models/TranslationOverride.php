<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An admin-edited translation that replaces the lang/<locale>.json value.
 */
class TranslationOverride extends Model
{
    /** @var list<string> */
    protected $fillable = ['locale', 'source_hash', 'source', 'value', 'updated_by_user_id'];
}
