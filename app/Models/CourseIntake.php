<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseIntake extends Model
{
    /** @var list<string> */
    protected $fillable = ['course_id', 'starts_on', 'application_deadline', 'is_open'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['course_id' => 'integer', 'starts_on' => 'date', 'application_deadline' => 'date', 'is_open' => 'boolean'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function label(): string
    {
        return $this->starts_on->format('F Y');
    }
}
