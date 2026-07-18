<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Concerns\ResolvesCurrentEmployee;
use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\AssignTeamMembersRequest;
use App\Http\Requests\Manager\StoreTeamRequest;
use App\Http\Requests\Manager\UpdateTeamRequest;
use App\Models\Employee;
use App\Models\Team;
use App\Services\ManagerTeamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeamController extends Controller
{
    use ResolvesCurrentEmployee;

    public function __construct(private readonly ManagerTeamService $teams)
    {
    }

    public function index(Request $request): View
    {
        $manager = $this->currentManagerOrNull();
        $departmentId = $manager ? $this->managedDepartmentId($manager) : null;

        $teamList = Team::query()
            ->with(['leader', 'department'])
            ->withCount('members')
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId), fn ($q) => $q->whereRaw('0 = 1'))
            ->orderBy('name')
            ->get();

        $unassignedCount = $manager
            ? $this->teams->unassignedEmployeeCount($manager)
            : 0;

        return view('manager.teams.index', compact('manager', 'teamList', 'unassignedCount', 'departmentId'));
    }

    public function create(): View
    {
        $manager = $this->currentManager();
        $leaders = $this->teams->availableLeaders($manager);
        $assignedLeaderIds = Team::query()
            ->where('department_id', $this->managedDepartmentId($manager))
            ->whereNotNull('leader_employee_id')
            ->pluck('leader_employee_id');

        return view('manager.teams.create', compact('manager', 'leaders', 'assignedLeaderIds'));
    }

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $manager = $this->currentManager();

        $team = $this->teams->createTeam($request->user(), $manager, $request->validated());

        return redirect()
            ->route('manager.teams.show', $team)
            ->with('success', 'Đã tạo nhóm "'.$team->name.'".');
    }

    public function show(Team $team): View
    {
        $manager = $this->currentManager();
        $this->teams->assertOwnsTeam($manager, $team);

        $team->load(['leader', 'department']);
        $members = $this->teams->members($team);
        $candidates = $this->teams->candidatesToAdd($manager, $team);

        return view('manager.teams.show', compact('team', 'members', 'candidates', 'manager'));
    }

    public function edit(Team $team): View
    {
        $manager = $this->currentManager();
        $this->teams->assertOwnsTeam($manager, $team);

        $leaders = $this->teams->availableLeaders($manager);
        $assignedLeaderIds = Team::query()
            ->where('department_id', $team->department_id)
            ->whereNotNull('leader_employee_id')
            ->where('id', '!=', $team->id)
            ->pluck('leader_employee_id');

        return view('manager.teams.edit', compact('team', 'leaders', 'assignedLeaderIds', 'manager'));
    }

    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        $manager = $this->currentManager();

        $this->teams->updateTeam($request->user(), $manager, $team, $request->validated());

        return redirect()
            ->route('manager.teams.show', $team)
            ->with('success', 'Đã cập nhật thông tin nhóm.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        $manager = $this->currentManager();
        $name = $team->name;

        $this->teams->deleteTeam($manager, $team);

        return redirect()
            ->route('manager.teams.index')
            ->with('success', 'Đã xóa nhóm "'.$name.'".');
    }

    public function assignMembers(AssignTeamMembersRequest $request, Team $team): RedirectResponse
    {
        $manager = $this->currentManager();

        $count = $this->teams->assignMembers(
            $manager,
            $team,
            array_map('intval', $request->input('employee_ids', [])),
        );

        return redirect()
            ->route('manager.teams.show', $team)
            ->with('success', $count > 0 ? "Đã thêm {$count} thành viên vào nhóm." : 'Không có thành viên mới được thêm.');
    }

    public function removeMember(Team $team, Employee $employee): RedirectResponse
    {
        $manager = $this->currentManager();
        $this->teams->removeMember($manager, $team, $employee);

        return redirect()
            ->route('manager.teams.show', $team)
            ->with('success', 'Đã gỡ '.$employee->full_name.' khỏi nhóm.');
    }
}
