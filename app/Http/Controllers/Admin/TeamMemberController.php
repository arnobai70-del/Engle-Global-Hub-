<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveTeamMemberRequest;
use App\Models\TeamMember;
use App\Services\ActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TeamMemberController extends Controller
{
    public function __construct(private ActivityRecorder $activity) {}

    public function index(): View
    {
        return view('admin.team-members.index', [
            'members' => TeamMember::query()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.team-members.form', ['member' => new TeamMember(['sort_order' => TeamMember::query()->max('sort_order') + 1])]);
    }

    public function store(SaveTeamMemberRequest $request): RedirectResponse
    {
        $member = new TeamMember;
        $this->save($member, $request);
        $this->activity->record($request->user(), 'team_member.created', $member, ['published' => $member->is_published]);

        return redirect()->route('admin.team-members.index')->with('status', 'Team member saved.');
    }

    public function edit(TeamMember $teamMember): View
    {
        return view('admin.team-members.form', ['member' => $teamMember]);
    }

    public function update(SaveTeamMemberRequest $request, TeamMember $teamMember): RedirectResponse
    {
        $this->save($teamMember, $request);
        $this->activity->record($request->user(), 'team_member.updated', $teamMember, ['published' => $teamMember->is_published]);

        return redirect()->route('admin.team-members.index')->with('status', 'Team member updated.');
    }

    public function destroy(TeamMember $teamMember): RedirectResponse
    {
        if ($teamMember->photo_path !== null) {
            Storage::disk('public')->delete($teamMember->photo_path);
        }
        $this->activity->record(request()->user(), 'team_member.deleted', $teamMember);
        $teamMember->delete();

        return redirect()->route('admin.team-members.index')->with('status', 'Team member deleted.');
    }

    private function save(TeamMember $member, SaveTeamMemberRequest $request): void
    {
        $member->fill($request->safe()->only(['name', 'role_title', 'bio', 'sort_order', 'is_published']));
        $oldPhoto = $member->photo_path;

        if ($request->hasFile('photo')) {
            $member->photo_path = $request->file('photo')->store('team', 'public');
        } elseif ($request->boolean('remove_photo')) {
            $member->photo_path = null;
        }

        $member->save();

        if ($oldPhoto !== null && $oldPhoto !== $member->photo_path) {
            Storage::disk('public')->delete($oldPhoto);
        }
    }
}
