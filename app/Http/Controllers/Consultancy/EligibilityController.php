<?php

namespace App\Http\Controllers\Consultancy;

use App\Http\Controllers\Controller;
use App\Models\EligibilityRule;
use App\Models\StudentEnquiry;
use App\Services\Consultancy\StudentLeadRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Study in Malaysia eligibility checker. Four questions; the answer lists the course
 * levels whose admin-entered rules (Admin › Eligibility rules) the student meets, then
 * the student can leave a name and phone number, saved as a Student Enquiry.
 */
class EligibilityController extends Controller
{
    /** English score options: IELTS band or equivalent. */
    public const ENGLISH_SCORES = ['4.0', '4.5', '5.0', '5.5', '6.0', '6.5', '7.0', '7.5', '8.0', '8.5', '9.0'];

    public function show(Request $request): View
    {
        $answers = $request->has('qualification') ? $this->answers($request) : null;

        return view('consultancy.eligibility', [
            'qualifications' => EligibilityRule::QUALIFICATIONS,
            'englishScores' => self::ENGLISH_SCORES,
            'answers' => $answers,
            'matches' => $answers !== null ? $this->matches($answers) : collect(),
        ]);
    }

    public function store(Request $request, StudentLeadRecorder $leads): RedirectResponse
    {
        $answers = $this->answers($request);
        $contact = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s().-]{7,32}$/'],
            'consent' => ['accepted'],
            'website' => ['prohibited'],
        ], [
            'phone.regex' => __('Enter a valid phone number, for example +8801712345678.'),
            'consent.accepted' => __('Please agree to be contacted about your enquiry.'),
        ]);
        $matches = $this->matches($answers);

        $lead = $leads->record([
            'full_name' => $contact['full_name'],
            'phone' => $contact['phone'],
            'destination' => 'MY',
            'highest_qualification' => EligibilityRule::QUALIFICATIONS[$answers['qualification']].', GPA '.number_format($answers['gpa'], 2),
            'study_level' => $matches->first()?->study_level,
            'message' => $this->summary($answers, $matches),
        ], 'Eligibility checker');

        return redirect()->route('consultancy.malaysia.eligibility', $this->query($answers))
            ->with('lead_reference', $lead->reference)
            ->withFragment('result');
    }

    /**
     * @return array{qualification: string, gpa: float, english: ?float, gap_years: int}
     */
    private function answers(Request $request): array
    {
        $data = $request->validate([
            'qualification' => ['required', Rule::in(array_keys(EligibilityRule::QUALIFICATIONS))],
            'gpa' => ['required', 'numeric', 'min:0', 'max:5'],
            'english' => ['nullable', Rule::in(self::ENGLISH_SCORES)],
            'gap_years' => ['required', 'integer', 'min:0', 'max:10'],
        ]);

        return [
            'qualification' => $data['qualification'],
            'gpa' => round((float) $data['gpa'], 2),
            'english' => filled($data['english'] ?? null) ? (float) $data['english'] : null,
            'gap_years' => (int) $data['gap_years'],
        ];
    }

    /**
     * Course levels the student meets the rules for, each level once (its first matching rule).
     *
     * @param  array{qualification: string, gpa: float, english: ?float, gap_years: int}  $answers
     * @return Collection<int, EligibilityRule>
     */
    private function matches(array $answers): Collection
    {
        return EligibilityRule::query()->active()->get()
            ->filter(fn (EligibilityRule $rule): bool => $rule->matches($answers))
            ->unique('study_level')->values();
    }

    /**
     * @param  array{qualification: string, gpa: float, english: ?float, gap_years: int}  $answers
     * @param  Collection<int, EligibilityRule>  $matches
     */
    private function summary(array $answers, Collection $matches): string
    {
        return implode("\n", [
            'Eligibility checker answers:',
            '- Highest qualification: '.EligibilityRule::QUALIFICATIONS[$answers['qualification']],
            '- Result (GPA/CGPA): '.number_format($answers['gpa'], 2),
            '- English (IELTS or equivalent): '.($answers['english'] !== null ? number_format($answers['english'], 1) : 'No test yet'),
            '- Years since last study: '.$answers['gap_years'],
            'Matching course levels: '.($matches->isEmpty() ? 'none' : $matches->map(fn (EligibilityRule $rule): string => StudentEnquiry::STUDY_LEVELS[$rule->study_level] ?? $rule->study_level)->implode(', ')),
        ]);
    }

    /**
     * @param  array{qualification: string, gpa: float, english: ?float, gap_years: int}  $answers
     * @return array<string, string|int|float>
     */
    private function query(array $answers): array
    {
        return array_filter([
            'qualification' => $answers['qualification'],
            'gpa' => $answers['gpa'],
            'english' => $answers['english'] !== null ? number_format($answers['english'], 1) : null,
            'gap_years' => $answers['gap_years'],
        ], static fn (mixed $value): bool => $value !== null);
    }
}
