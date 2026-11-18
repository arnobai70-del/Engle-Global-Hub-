<?php

namespace App\Http\Controllers\Consultancy;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\Institution;
use App\Models\StudentEnquiry;
use App\Services\Consultancy\MalaysiaCostCalculator;
use App\Services\Consultancy\StudentLeadRecorder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Study in Malaysia course finder: active courses of publicly listed Malaysian institutions
 * (Admin › Courses & Intakes), filtered by level, subject, yearly budget and intake month.
 */
class CourseFinderController extends Controller
{
    public function index(Request $request, MalaysiaCostCalculator $costs): View
    {
        $filters = $request->validate([
            'level' => ['nullable', Rule::in(array_keys(StudentEnquiry::STUDY_LEVELS))],
            'subject' => ['nullable', 'string', 'max:80'],
            'budget' => ['nullable', 'integer', 'min:1'],
            'intake' => ['nullable', 'date_format:Y-m'],
        ]);

        $courses = $this->malaysianCourses()
            ->when($filters['level'] ?? null, fn (Builder $query, string $level) => $query->where('study_level', $level))
            ->when($filters['subject'] ?? null, fn (Builder $query, string $subject) => $query->where('subject', $subject))
            // Courses without a published fee cannot be compared with a budget, so they are left out.
            ->when($filters['budget'] ?? null, fn (Builder $query, int|string $budget) => $query->whereNotNull('tuition_fee_myr')->where('tuition_fee_myr', '<=', (int) $budget))
            ->when($filters['intake'] ?? null, fn (Builder $query, string $intake) => $query->whereHas('intakes', fn (Builder $intakes) => $this->openIntakes($intakes)
                ->whereBetween('starts_on', [$intake.'-01', date('Y-m-t', (int) strtotime($intake.'-01'))])))
            ->with(['institution:id,name', 'intakes' => fn ($query) => $this->openIntakes($query)->orderBy('starts_on')])
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('consultancy.courses', [
            'courses' => $courses,
            'filters' => $filters,
            'levels' => $this->malaysianCourses()->whereNotNull('study_level')->distinct()->pluck('study_level')
                ->mapWithKeys(fn (string $level): array => [$level => StudentEnquiry::STUDY_LEVELS[$level] ?? $level])->all(),
            'subjects' => $this->malaysianCourses()->whereNotNull('subject')->distinct()->orderBy('subject')->pluck('subject')->all(),
            'intakes' => $this->openIntakes(CourseIntake::query())->whereIn('course_id', $this->malaysianCourses()->select('id'))
                ->orderBy('starts_on')->get(['starts_on'])->mapWithKeys(fn (CourseIntake $intake): array => [$intake->starts_on->format('Y-m') => $intake->starts_on->translatedFormat('F Y')])->all(),
            'hasFees' => $this->malaysianCourses()->whereNotNull('tuition_fee_myr')->exists(),
            'rate' => $costs->rate(),
            'selectedCourse' => $request->integer('course') ?: null,
        ]);
    }

    public function enquire(Request $request, StudentLeadRecorder $leads): RedirectResponse
    {
        $data = $request->validate([
            'course_id' => ['nullable', 'integer', Rule::exists('courses', 'id')],
            'full_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s().-]{7,32}$/'],
            'consent' => ['accepted'],
            'website' => ['prohibited'],
        ], [
            'phone.regex' => __('Enter a valid phone number, for example +8801712345678.'),
            'consent.accepted' => __('Please agree to be contacted about your enquiry.'),
        ]);
        $course = filled($data['course_id'] ?? null) ? $this->malaysianCourses()->with('institution:id,name')->find($data['course_id']) : null;

        $lead = $leads->record([
            'full_name' => $data['full_name'],
            'phone' => $data['phone'],
            'destination' => 'MY',
            'study_level' => $course?->study_level,
            'message' => $course !== null ? 'Course finder: interested in "'.$course->name.'" at '.$course->institution?->name.' (course #'.$course->id.').' : 'Course finder: asked for course advice.',
        ], 'Course finder');

        return redirect()->to(url()->previous(route('consultancy.malaysia.courses')).'#ask')->with('lead_reference', $lead->reference);
    }

    /**
     * @return Builder<Course>
     */
    private function malaysianCourses(): Builder
    {
        return Course::query()->where('is_active', true)
            ->whereIn('institution_id', Institution::query()->publiclyListed()->whereHas('country', fn ($query) => $query->where('iso2', 'MY'))->select('id'));
    }

    /**
     * Intakes still open for application, from this month on.
     *
     * @param  Builder<CourseIntake>|HasMany<CourseIntake, Course>  $query
     * @return Builder<CourseIntake>|HasMany<CourseIntake, Course>
     */
    private function openIntakes(Builder|HasMany $query): Builder|HasMany
    {
        return $query->where('is_open', true)->where('starts_on', '>=', now()->startOfMonth()->toDateString());
    }
}
