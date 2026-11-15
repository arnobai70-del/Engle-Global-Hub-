<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\Institution;
use App\Models\StudentEnquiry;
use App\Services\ActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function __construct(private ActivityRecorder $activity) {}

    public function index(Request $request): View
    {
        $institutionId = $request->integer('institution') ?: null;
        $search = trim((string) $request->query('search', ''));

        return view('admin.courses.index', [
            'courses' => Course::query()->with('institution:id,name')->withCount(['intakes' => fn ($query) => $query->where('is_open', true)])
                ->when($institutionId, fn ($query) => $query->where('institution_id', $institutionId))
                ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
                ->orderBy('name')->paginate(25)->withQueryString(),
            'institutions' => Institution::query()->orderBy('name')->get(['id', 'name']),
            'filters' => ['institution' => $institutionId, 'search' => $search],
            'levels' => StudentEnquiry::STUDY_LEVELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $course = Course::query()->create($this->validated($request, null));
        $this->activity->record($request->user(), 'course.created', $course, ['name' => $course->name]);

        return redirect()->route('admin.courses.edit', $course)->with('status', 'Course added. Add its intakes below.');
    }

    public function edit(Course $course): View
    {
        return view('admin.courses.edit', [
            'course' => $course->load(['institution:id,name', 'intakes']),
            'institutions' => Institution::query()->orderBy('name')->get(['id', 'name']),
            'levels' => StudentEnquiry::STUDY_LEVELS,
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $course->update($this->validated($request, $course));
        $this->activity->record($request->user(), 'course.updated', $course, ['name' => $course->name]);

        return redirect()->route('admin.courses.edit', $course)->with('status', 'Course updated.');
    }

    public function storeIntake(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'starts_on' => ['required', 'date_format:Y-m'],
            'application_deadline' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $startsOn = $data['starts_on'].'-01';

        if ($course->intakes()->whereDate('starts_on', $startsOn)->exists()) {
            return back()->withErrors(['starts_on' => 'This course already has an intake in that month.']);
        }

        $course->intakes()->create([
            'starts_on' => $startsOn,
            'application_deadline' => $data['application_deadline'] ?? null,
            'is_open' => true,
        ]);

        return redirect()->route('admin.courses.edit', $course)->with('status', 'Intake added.');
    }

    public function updateIntake(Request $request, Course $course, CourseIntake $intake): RedirectResponse
    {
        abort_unless((int) $intake->course_id === (int) $course->getKey(), 404);
        $intake->update(['is_open' => $request->boolean('is_open')]);

        return redirect()->route('admin.courses.edit', $course)->with('status', 'Intake updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Course $course): array
    {
        $request->merge(['is_active' => $request->boolean('is_active', $course === null)]);

        return $request->validate([
            'institution_id' => ['required', 'integer', Rule::exists('institutions', 'id')],
            'name' => ['required', 'string', 'max:200', Rule::unique('courses', 'name')->where('institution_id', $request->input('institution_id'))->ignore($course)],
            'study_level' => ['nullable', Rule::in(array_keys(StudentEnquiry::STUDY_LEVELS))],
            'duration_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'tuition_note' => ['nullable', 'string', 'max:300'],
            'source_url' => ['nullable', 'url:https', 'max:2048'],
            'is_active' => ['boolean'],
        ], ['name.unique' => 'This institution already has a course with that name.']);
    }
}
