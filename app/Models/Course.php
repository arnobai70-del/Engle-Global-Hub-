<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A course offered by an institution, maintained by staff from verified
 * institution information. Fees are free-text notes, never calculated.
 */
class Course extends Model
{
    /** @var list<string> */
    protected $fillable = ['institution_id', 'name', 'study_level', 'duration_months', 'tuition_note', 'source_url', 'is_active'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['institution_id' => 'integer', 'duration_months' => 'integer', 'is_active' => 'boolean'];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function intakes(): HasMany
    {
        return $this->hasMany(CourseIntake::class)->orderBy('starts_on');
    }
}
