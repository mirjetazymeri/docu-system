<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Application;
use Sentinel;

trait AuthorizesApplicationAccess
{
    protected function authorizeApplicationView(Application $application): void
    {
        $user = Sentinel::getUser();
        abort_unless($user, 403);

        if (Sentinel::inRole('admin')) {
            return;
        }

        /*
         * Documentation workers may read every project. Editing remains
         * restricted to projects explicitly assigned to them below.
         */
        if ($this->isDocumentationWorker($user)) {
            return;
        }

        abort_unless(
            $this->isAssignedToApplication($application, $user->id),
            403,
            __('ui.you_are_not_assigned_to_this_project')
        );
    }

    protected function authorizeApplicationEdit(
        Application $application,
        string $permission
    ): void {
        $this->authorizeApplicationView($application);

        if (Sentinel::inRole('admin')) {
            return;
        }

        $user = Sentinel::getUser();
        $assignment = $this->applicationAssignment(
            $application,
            $user->id
        );

        abort_unless(
            $assignment &&
            (bool) $assignment->pivot->can_edit &&
            $user->hasAccess($permission),
            403,
            __('ui.you_do_not_have_permission_to_edit_this_project')
        );
    }

    protected function authorizeApplicationPermission(
        Application $application,
        string $permission
    ): void {
        $this->authorizeApplicationView($application);

        if (Sentinel::inRole('admin')) {
            return;
        }

        abort_unless(
            Sentinel::getUser()->hasAccess($permission),
            403,
            __('ui.you_do_not_have_permission_to_access_this_part_of_the_project')
        );
    }

    protected function canEditApplication(
        Application $application,
        string $permission
    ): bool {
        $user = Sentinel::getUser();

        if (!$user) {
            return false;
        }

        if (Sentinel::inRole('admin')) {
            return true;
        }

        $assignment = $this->applicationAssignment(
            $application,
            $user->id
        );

        return (bool) (
            $assignment &&
            $assignment->pivot->can_edit &&
            $user->hasAccess($permission)
        );
    }

    protected function authorizeApplicationAdministrator(): void
    {
        abort_unless(
            Sentinel::check() && Sentinel::inRole('admin'),
            403,
            __('ui.only_an_administrator_can_perform_this_action')
        );
    }

    protected function isDocumentationWorker($user): bool
    {
        return $user->accessLevels()
            ->where('access_levels.name', 'Documentation Worker')
            ->exists();
    }

    protected function isAssignedToApplication(
        Application $application,
        int $userId
    ): bool {
        return (bool) $this->applicationAssignment(
            $application,
            $userId
        );
    }

    private function applicationAssignment(
        Application $application,
        int $userId
    ) {
        return $application->users()
            ->where('users.id', $userId)
            ->first();
    }
}
