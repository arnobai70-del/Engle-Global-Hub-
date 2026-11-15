<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardProgram;
use App\Models\RewardTransaction;
use App\Models\User;
use App\Services\ActivityRecorder;
use App\Services\RewardLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RewardProgramController extends Controller
{
    public function __construct(private ActivityRecorder $activity) {}

    public function index(): View
    {
        return view('admin.rewards.index', [
            'programs' => RewardProgram::query()->withCount('transactions')->orderByDesc('id')->get(),
            'recent' => RewardTransaction::query()->with(['user:id,name,email', 'program:id,name', 'createdBy:id,name'])->latest('id')->limit(50)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $program = RewardProgram::query()->create([...$this->validated($request, null), 'is_published' => false]);
        $this->activity->record($request->user(), 'reward_program.created', $program, ['slug' => $program->slug]);

        return back()->with('status', 'Programme saved as unpublished.');
    }

    public function update(Request $request, RewardProgram $program): RedirectResponse
    {
        $data = $this->validated($request, $program);
        $program->fill($data);
        $publish = $request->boolean('is_published');
        if ($publish && ! $program->isReadyToPublish()) {
            return back()->withErrors(['terms' => 'Write the terms and the earning rules before publishing the programme.']);
        }
        $program->is_published = $publish;
        $program->save();
        $this->activity->record($request->user(), 'reward_program.updated', $program, ['published' => $program->is_published]);

        return back()->with('status', 'Programme updated.');
    }

    public function adjust(Request $request, RewardLedger $ledger): RedirectResponse
    {
        $data = $request->validate([
            'reward_program_id' => ['required', 'integer', Rule::exists(RewardProgram::class, 'id')],
            'customer_email' => ['required', 'email', Rule::exists(User::class, 'email')],
            'points' => ['required', 'integer', 'between:-100000,100000'],
            'reason' => ['required', 'string', 'max:200'],
        ]);

        $ledger->adjust(
            User::query()->where('email', $data['customer_email'])->firstOrFail(),
            RewardProgram::query()->findOrFail($data['reward_program_id']),
            (int) $data['points'],
            $data['reason'],
            $request->user(),
        );

        return back()->with('status', 'Points recorded.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?RewardProgram $program): array
    {
        $request->merge(['slug' => Str::slug((string) ($request->input('slug') ?: $request->input('name')))]);

        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:160', Rule::unique('reward_programs', 'slug')->ignore($program)],
            'description' => ['nullable', 'string', 'max:1000'],
            'terms' => ['nullable', 'string', 'max:10000'],
            'earning_rules' => ['nullable', 'string', 'max:5000'],
            'points_expire_after_days' => ['nullable', 'integer', 'between:1,3650'],
            'starts_on' => ['nullable', 'date_format:Y-m-d'],
            'ends_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
        ]);
    }
}
