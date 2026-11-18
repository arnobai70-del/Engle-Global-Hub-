<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EligibilityRule;
use App\Models\StudentEnquiry;
use App\Services\ActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin › Eligibility rules: the entry rules behind the public eligibility checker.
 * The checker page stays hidden until at least one active rule exists.
 */
class EligibilityRuleController extends Controller
{
    public function __construct(private ActivityRecorder $activity) {}

    public function index(): View
    {
        return view('admin.eligibility-rules.index', [
            'rules' => EligibilityRule::query()->orderBy('sort_order')->orderBy('id')->get(),
            'levels' => StudentEnquiry::STUDY_LEVELS,
            'qualifications' => EligibilityRule::QUALIFICATIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rule = EligibilityRule::query()->create($this->validated($request));
        $this->activity->record($request->user(), 'eligibility-rule.created', $rule, ['level' => $rule->study_level]);

        return redirect()->route('admin.eligibility-rules.index')->with('status', 'Rule added. The eligibility checker uses it right away.');
    }

    public function edit(EligibilityRule $rule): View
    {
        return view('admin.eligibility-rules.edit', [
            'rule' => $rule,
            'levels' => StudentEnquiry::STUDY_LEVELS,
            'qualifications' => EligibilityRule::QUALIFICATIONS,
        ]);
    }

    public function update(Request $request, EligibilityRule $rule): RedirectResponse
    {
        $rule->update($this->validated($request));
        $this->activity->record($request->user(), 'eligibility-rule.updated', $rule, ['level' => $rule->study_level]);

        return redirect()->route('admin.eligibility-rules.index')->with('status', 'Rule saved.');
    }

    public function destroy(Request $request, EligibilityRule $rule): RedirectResponse
    {
        $this->activity->record($request->user(), 'eligibility-rule.deleted', $rule, ['level' => $rule->study_level]);
        $rule->delete();

        return redirect()->route('admin.eligibility-rules.index')->with('status', 'Rule deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'study_level' => ['required', Rule::in(array_keys(StudentEnquiry::STUDY_LEVELS))],
            'qualifications' => ['required', 'array', 'min:1'],
            'qualifications.*' => ['string', Rule::in(array_keys(EligibilityRule::QUALIFICATIONS))],
            'min_gpa' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'min_english' => ['nullable', 'numeric', 'min:0', 'max:9'],
            'max_gap_years' => ['nullable', 'integer', 'min:0', 'max:30'],
            'note_en' => ['nullable', 'string', 'max:300'],
            'note_bn' => ['nullable', 'string', 'max:300'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ], ['qualifications.required' => 'Tick at least one qualification this rule applies to.']);

        return [
            'study_level' => $data['study_level'],
            'qualifications' => array_values(array_unique($data['qualifications'])),
            'min_gpa' => $data['min_gpa'] ?? null,
            'min_english' => $data['min_english'] ?? null,
            'max_gap_years' => $data['max_gap_years'] ?? null,
            'note_en' => filled($data['note_en'] ?? null) ? trim(strip_tags((string) $data['note_en'])) : null,
            'note_bn' => filled($data['note_bn'] ?? null) ? trim(strip_tags((string) $data['note_bn'])) : null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'sort_order' => (int) $data['sort_order'],
        ];
    }
}
