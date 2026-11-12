<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\User;
use Illuminate\Http\Request;

class DocumentationWorkerController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'worker_id' => ['nullable', 'integer', 'exists:users,id'],
            'application_id' => ['nullable', 'integer', 'exists:applications,id'],
            'status' => ['nullable', 'in:in_process,completed'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $applicationId = $filters['application_id'] ?? null;
        $status = $filters['status'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        $documentationWorkers = User::query()
            ->whereHas('accessLevels', fn ($query) =>
                $query->where('access_levels.name', 'Documentation Worker'))
            ->when($filters['worker_id'] ?? null, fn ($query, $workerId) =>
                $query->where('users.id', $workerId))
            ->when($applicationId, fn ($query, $projectId) =>
                $query->whereHas('applications', fn ($applicationQuery) =>
                    $applicationQuery->where('applications.id', $projectId)))
            ->when($status, fn ($query, $selectedStatus) =>
                $query->whereHas('applications', fn ($applicationQuery) =>
                    $applicationQuery->where('documentation_status', $selectedStatus)
                        ->when($applicationId, fn ($q, $id) =>
                            $q->where('applications.id', $id))))
            ->when($dateFrom || $dateTo, function ($query) use ($dateFrom, $dateTo) {
                $query->whereHas('documentationWorkerHistories', function ($historyQuery) use (
                    $dateFrom,
                    $dateTo
                ) {
                    $historyQuery
                        ->when($dateFrom, fn ($q, $date) => $q->whereDate('occurred_at', '>=', $date))
                        ->when($dateTo, fn ($q, $date) => $q->whereDate('occurred_at', '<=', $date));
                });
            })
            ->withCount([
                'applications as assigned_projects_count' => fn ($query) =>
                    $query->when($applicationId, fn ($q, $id) =>
                        $q->where('applications.id', $id)),
                'applications as completed_documentations_count' => function ($query) use ($applicationId) {
                    $query->where('documentation_status', 'completed')
                        ->when($applicationId, fn ($q, $id) =>
                            $q->where('applications.id', $id));
                },
                'applications as in_process_documentations_count' => function ($query) use ($applicationId) {
                    $query->where('documentation_status', 'in_process')
                        ->when($applicationId, fn ($q, $id) =>
                            $q->where('applications.id', $id));
                },
            ])
            ->with([
                'documentationWorkerHistories' => function ($query) use (
                    $applicationId,
                    $dateFrom,
                    $dateTo
                ) {
                    $query->with('application:id,name')
                        ->when($applicationId, fn ($q, $id) => $q->where('application_id', $id))
                        ->when($dateFrom, fn ($q, $date) => $q->whereDate('occurred_at', '>=', $date))
                        ->when($dateTo, fn ($q, $date) => $q->whereDate('occurred_at', '<=', $date))
                        ->latest('occurred_at');
                },
            ])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->each(function ($worker) {
                $worker->last_worker_activity = $worker->documentationWorkerHistories->first();
            });

        return view('admin.documentation_workers.index', [
            'documentationWorkers' => $documentationWorkers,
            'workerOptions' => User::query()
                ->whereHas('accessLevels', fn ($query) =>
                    $query->where('access_levels.name', 'Documentation Worker'))
                ->orderBy('first_name')->orderBy('last_name')->get(),
            'applicationOptions' => Application::orderBy('name')->get(['id', 'name']),
            'workerFilters' => $filters,
        ]);
    }

    public function show(Request $request, User $user)
    {
        abort_unless(
            $user->accessLevels()
                ->where('access_levels.name', 'Documentation Worker')
                ->exists(),
            404
        );

        $filters = $request->validate([
            'application_id' => ['nullable', 'integer', 'exists:applications,id'],
            'status' => ['nullable', 'in:in_process,completed'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $applicationId = $filters['application_id'] ?? null;
        $status = $filters['status'] ?? null;

        $histories = $user->documentationWorkerHistories()
            ->with(['application:id,name', 'unit:id,name', 'documentation:id,title,status'])
            ->when($applicationId, fn ($query, $id) => $query->where('application_id', $id))
            ->when($status, fn ($query, $selectedStatus) =>
                $query->whereHas('application', fn ($applicationQuery) =>
                    $applicationQuery->where('documentation_status', $selectedStatus)))
            ->when($filters['date_from'] ?? null, fn ($query, $date) =>
                $query->whereDate('occurred_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) =>
                $query->whereDate('occurred_at', '<=', $date))
            ->latest('occurred_at')
            ->paginate(25)
            ->withQueryString();

        $projects = $user->applications()
            ->when($applicationId, fn ($query, $id) =>
                $query->where('applications.id', $id));

        return view('admin.documentation_workers.show', [
            'worker' => $user,
            'histories' => $histories,
            'filters' => $filters,
            'applicationOptions' => Application::query()
                ->whereHas('users', fn ($query) => $query->where('users.id', $user->id))
                ->orderBy('name')
                ->get(['id', 'name']),
            'assignedProjectsCount' => $user->applications()
                ->when($applicationId, fn ($query, $id) => $query->where('applications.id', $id))
                ->count(),
            'completedCount' => (clone $projects)->where('documentation_status', 'completed')->count(),
            'inProcessCount' => (clone $projects)->where('documentation_status', 'in_process')->count(),
        ]);
    }
}
