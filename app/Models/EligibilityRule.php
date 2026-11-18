<?php

namespace App\Models;

use Database\Factories\EligibilityRuleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One admin-entered rule of the eligibility checker: the course level a student may apply
 * for when their answers meet every limit that is filled in.
 *
 * @property list<string> $qualifications
 */
class EligibilityRule extends Model
{
    /** @use HasFactory<EligibilityRuleFactory> */
    use HasFactory;

    /** Highest completed qualification, as asked on the checker. */
    public const QUALIFICATIONS = [
        'ssc' => 'SSC / O-Level',
        'hsc' => 'HSC / A-Level',
        'diploma' => 'Diploma',
        'bachelor' => "Bachelor's degree",
        'master' => "Master's degree",
    ];

    protected $fillable = [
        'study_level', 'qualifications', 'min_gpa', 'min_english', 'max_gap_years', 'note_en', 'note_bn', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'qualifications' => 'array',
            'min_gpa' => 'float',
            'min_english' => 'float',
            'max_gap_years' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<EligibilityRule>  $query
     * @return Builder<EligibilityRule>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @param  array{qualification: string, gpa: float, english: ?float, gap_years: int}  $answers
     */
    public function matches(array $answers): bool
    {
        if (! in_array($answers['qualification'], $this->qualifications ?? [], true)) {
            return false;
        }
        if ($this->min_gpa !== null && $answers['gpa'] < $this->min_gpa) {
            return false;
        }
        if ($this->min_english !== null && ($answers['english'] === null || $answers['english'] < $this->min_english)) {
            return false;
        }

        return $this->max_gap_years === null || $answers['gap_years'] <= $this->max_gap_years;
    }

    public function levelLabel(): string
    {
        return __(StudentEnquiry::STUDY_LEVELS[$this->study_level] ?? $this->study_level);
    }

    public function note(): ?string
    {
        $note = app()->getLocale() === 'bn' && filled($this->note_bn) ? $this->note_bn : $this->note_en;

        return filled($note) ? (string) $note : null;
    }
}
