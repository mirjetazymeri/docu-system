<?php

namespace App\Http\Controllers;

use App\Models\AppCategory;
use App\Models\Application;
use App\Models\DocumentationWorkerHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Sentinel;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $user = Sentinel::getUser();
        abort_unless($user, 403);

        $categoryFilter = $request->get('category_id');
        $categories = AppCategory::all();
        $canManageApplications = Sentinel::inRole('admin');
        $isDocumentationWorker = !$canManageApplications
            && $this->isDocumentationWorker($user);
        $canUpdateDocumentationStatus = $isDocumentationWorker;
        $applicationScope = $request->query('scope', 'all');

        if (!in_array($applicationScope, ['all', 'assigned'], true)) {
            $applicationScope = 'all';
        }

        $applications = Application::query()
            ->with(['category', 'users:id,first_name,last_name'])
            ->when(
                !$canManageApplications &&
                    (
                        !$isDocumentationWorker ||
                        $applicationScope === 'assigned'
                    ),
                function ($query) use ($user) {
                    $query->whereHas('users', function ($userQuery) use ($user) {
                        $userQuery->where('users.id', $user->id);
                    });
                }
            )
            ->when($categoryFilter, function ($query) use ($categoryFilter) {
                $query->where('category_id', $categoryFilter);
            })
            ->latest('id')
            ->get();

        $assignedApplicationIds = $applications
            ->filter(
                fn (Application $application) =>
                    $application->users->contains('id', $user->id)
            )
            ->pluck('id');

        return view('admin.applications.index', compact(
            'applications',
            'categories',
            'canManageApplications',
            'canUpdateDocumentationStatus',
            'isDocumentationWorker',
            'assignedApplicationIds',
            'applicationScope'
        ));
    }

    public function create()
    {
        $this->authorizeAdministrator();

        return view('admin.applications.create', [
            'categories' => AppCategory::all(),
            'users' => $this->assignableUsers(),
        ]);
    }

    public function status()
    {
        $this->authorizeAdministrator();

        $applications = Application::query()
            ->with(['category', 'users:id,first_name,last_name'])
            ->withCount([
                'applicationTestings as completed_testings_count' => fn ($query) =>
                    $query->where('is_completed', true),
            ])
            ->orderBy('name')
            ->get();

        return view('admin.applications.status', compact('applications'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdministrator();
        $validated = $this->validateApplication($request);

        $application = Application::create([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
        ]);

        $this->syncAssignedUsers(
            $application,
            $validated['assigned_user_ids'] ?? []
        );

        return redirect()->route('applications.index')
            ->with('success', __('ui.application_created_and_users_assigned_successfully'));
    }

    public function edit(Application $application)
    {
        $this->authorizeAdministrator();
        $application->load('users');

        return view('admin.applications.edit', [
            'application' => $application,
            'categories' => AppCategory::all(),
            'users' => $this->assignableUsers($application),
            'assignedUserIds' => $application->users->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Application $application)
    {
        $this->authorizeAdministrator();
        $validated = $this->validateApplication($request);

        $application->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
        ]);

        $this->syncAssignedUsers(
            $application,
            $validated['assigned_user_ids'] ?? []
        );

        return redirect()->route('applications.index')
            ->with('success', __('ui.application_and_assigned_users_updated_successfully'));
    }

    public function destroy(Application $application)
    {
        $this->authorizeAdministrator();
        $application->delete();

        return redirect()->route('applications.index')
            ->with('success', __('ui.application_deleted_successfully'));
    }

    public function updateDocumentationStatus(
        Request $request,
        Application $application
    ) {
        $user = Sentinel::getUser();
        abort_unless($user, 403);

        $isDocumentationWorker = $user->accessLevels()
            ->where('access_levels.name', 'Documentation Worker')
            ->exists();
        $isAssignedToApplication = $application->users()
            ->where('users.id', $user->id)
            ->exists();
        $canUpdate = $isDocumentationWorker && $isAssignedToApplication;
        abort_unless($canUpdate, 403);

        $validated = $request->validate([
            'documentation_status' => ['required', 'in:in_process,completed'],
        ]);

        $previousStatus = $application->documentation_status;
        $newStatus = $validated['documentation_status'];

        if ($previousStatus === $newStatus) {
            return back();
        }

        $application->update([
            'documentation_status' => $newStatus,
            'documentation_completed_at' => $newStatus === 'completed' ? now() : null,
            'documentation_completed_by' => $newStatus === 'completed' ? $user->id : null,
        ]);

        DocumentationWorkerHistory::create([
            'user_id' => $user->id,
            'application_id' => $application->id,
            'event' => $newStatus === 'completed'
                ? 'project_completed'
                : 'project_reopened',
            'metadata' => ['previous_status' => $previousStatus],
            'occurred_at' => now(),
        ]);

        return back()->with(
            'success',
            $newStatus === 'completed'
                ? __('documentation_workers.project_marked_completed')
                : __('documentation_workers.project_marked_in_process')
        );
    }

    private function validateApplication(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:app_categories,id'],
            'assigned_user_ids' => ['nullable', 'array'],
            'assigned_user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);
    }

    private function assignableUsers(?Application $application = null)
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'user'))
            ->where(function ($query) use ($application) {
                $query->whereHas('accessLevels', function ($accessQuery) {
                    $accessQuery->where(
                        'access_levels.name',
                        'Documentation Worker'
                    );
                });

                if ($application) {
                    $query->orWhereHas('applications', function ($appQuery) use ($application) {
                        $appQuery->where(
                            'applications.id',
                            $application->id
                        );
                    });
                }
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);
    }

    private function syncAssignedUsers(
        Application $application,
        array $userIds
    ): void {
        $previousUserIds = $application->users()->pluck('users.id');
        $assignments = collect($userIds)
            ->unique()
            ->mapWithKeys(fn ($userId) => [
                $userId => ['can_edit' => true],
            ])
            ->all();

        $application->users()->sync($assignments);

        collect(array_keys($assignments))
            ->diff($previousUserIds)
            ->each(function ($userId) use ($application) {
                DocumentationWorkerHistory::create([
                    'user_id' => $userId,
                    'application_id' => $application->id,
                    'event' => 'assigned',
                    'occurred_at' => now(),
                ]);
            });
    }

    private function authorizeAdministrator(): void
    {
        abort_unless(Sentinel::check() && Sentinel::inRole('admin'), 403);
    }

    private function isDocumentationWorker(User $user): bool
    {
        return $user->accessLevels()
            ->where('access_levels.name', 'Documentation Worker')
            ->exists();
    }
}
