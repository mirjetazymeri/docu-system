<?php

namespace App\Http\Controllers;

use App\Mail\TestingIssueReadyForReview;
use App\Mail\TestingIssueReviewed;
use App\Models\Application;
use App\Models\ApplicationTestingDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Sentinel;
use Throwable;

class TestingIssueController extends Controller
{
    public function index(Request $request)
    {
        $user = Sentinel::getUser();
        abort_unless($user, 403);

        $isAdmin = Sentinel::inRole('admin');
        $isDocumentationWorker = $this->isDocumentationWorker($user);
        abort_unless($isAdmin || $isDocumentationWorker, 403);

        $filters = $request->validate([
            'application_id' => ['nullable', 'integer', 'exists:applications,id'],
            'fix_status' => [
                'nullable',
                'in:active,open,pending_review,reopened,verified,all',
            ],
        ]);
        $selectedStatus = $filters['fix_status'] ?? 'active';

        $issues = ApplicationTestingDetail::query()
            ->where(function ($query) {
                $query->whereIn('result', ['not_passed', 'in_process'])
                    ->orWhereHas('activities');
            })
            ->when(!$isAdmin, function ($query) use ($user) {
                $query->whereNotNull('reported_at')->whereHas(
                    'applicationTesting.application.users',
                    fn ($userQuery) => $userQuery->where('users.id', $user->id)
                );
            })
            ->when(
                $filters['application_id'] ?? null,
                fn ($query, $applicationId) => $query->whereHas(
                    'applicationTesting',
                    fn ($testingQuery) => $testingQuery->where(
                        'application_id',
                        $applicationId
                    )
                )
            )
            ->when($selectedStatus !== 'all', function ($query) use ($selectedStatus) {
                if ($selectedStatus === 'active') {
                    $query->whereIn(
                        'fix_status',
                        ['open', 'pending_review', 'reopened']
                    );
                    return;
                }

                $query->where('fix_status', $selectedStatus);
            })
            ->with([
                'applicationTesting.application:id,name',
                'unit:id,name',
                'fixedBy:id,first_name,last_name',
                'reportedBy:id,first_name,last_name',
                'reviewedBy:id,first_name,last_name',
                'activities.user:id,first_name,last_name',
            ])
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        $applicationOptions = Application::query()
            ->when(!$isAdmin, fn ($query) => $query->whereHas(
                'users',
                fn ($userQuery) => $userQuery->where('users.id', $user->id)
            ))
            ->whereHas('applicationTestings.testingDetails', function ($query) use ($isAdmin) {
                $query->whereIn('result', ['not_passed', 'in_process'])
                    ->orWhereHas('activities');

                if (!$isAdmin) {
                    $query->whereNotNull('reported_at');
                }
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.testing_issues.index', [
            'issues' => $issues,
            'applicationOptions' => $applicationOptions,
            'filters' => $filters,
            'selectedStatus' => $selectedStatus,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function submitFix(
        Request $request,
        ApplicationTestingDetail $testingIssue
    ) {
        $user = Sentinel::getUser();
        abort_unless($user && $this->isDocumentationWorker($user), 403);

        $testingIssue->loadMissing('applicationTesting.application');
        $this->authorizeWorkerAssignment($testingIssue, $user->id);

        abort_unless(
            in_array($testingIssue->result, ['not_passed', 'in_process'], true) &&
            in_array($testingIssue->fix_status, ['open', 'reopened'], true),
            422,
            __('testing_issues.invalid_submission_state')
        );

        $validated = $request->validate([
            'fix_comment' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($testingIssue, $user, $validated) {
            $testingIssue->update([
                'fix_status' => 'pending_review',
                'fix_comment' => $validated['fix_comment'],
                'fixed_by' => $user->id,
                'fixed_at' => now(),
                'reviewed_by' => null,
                'reviewed_at' => null,
                'review_comment' => null,
            ]);

            $testingIssue->activities()->create([
                'user_id' => $user->id,
                'event' => 'submitted_for_review',
                'comment' => $validated['fix_comment'],
                'occurred_at' => now(),
            ]);
        });

        $testingIssue->loadMissing([
            'applicationTesting.application',
            'unit',
            'fixedBy',
        ]);
        [$sentCount, $failedCount] = $this->notifySuperAdmins($testingIssue);

        $response = back()->with(
            'success',
            __('testing_issues.submitted_successfully')
        );

        if ($sentCount === 0) {
            return $response->with(
                'warning',
                __('testing_issues.admin_notification_failed')
            );
        }

        if ($failedCount > 0) {
            return $response->with(
                'warning',
                __('testing_issues.admin_notification_partial', [
                    'sent' => $sentCount,
                    'failed' => $failedCount,
                ])
            );
        }

        return $response->with(
            'mail_success',
            trans_choice(
                'testing_issues.admins_notified',
                $sentCount,
                ['count' => $sentCount]
            )
        );
    }

    public function verify(
        Request $request,
        ApplicationTestingDetail $testingIssue
    ) {
        $this->authorizeAdmin();
        abort_unless(
            $testingIssue->fix_status === 'pending_review',
            422,
            __('testing_issues.invalid_review_state')
        );

        $validated = $request->validate([
            'review_comment' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($testingIssue, $validated) {
            $testingIssue->update([
                'result' => 'passed',
                'fix_status' => 'verified',
                'reviewed_by' => Sentinel::getUser()->id,
                'reviewed_at' => now(),
                'review_comment' => $validated['review_comment'] ?? null,
            ]);

            $testingIssue->activities()->create([
                'user_id' => Sentinel::getUser()->id,
                'event' => 'verified',
                'comment' => $validated['review_comment'] ?? null,
                'occurred_at' => now(),
            ]);
        });

        return $this->reviewResponse(
            $testingIssue,
            'verified',
            __('testing_issues.verified_successfully')
        );
    }

    public function reopen(
        Request $request,
        ApplicationTestingDetail $testingIssue
    ) {
        $this->authorizeAdmin();
        abort_unless(
            $testingIssue->fix_status === 'pending_review',
            422,
            __('testing_issues.invalid_review_state')
        );

        $validated = $request->validate([
            'review_comment' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($testingIssue, $validated) {
            $testingIssue->update([
                'fix_status' => 'reopened',
                'reviewed_by' => Sentinel::getUser()->id,
                'reviewed_at' => now(),
                'review_comment' => $validated['review_comment'],
            ]);

            $testingIssue->activities()->create([
                'user_id' => Sentinel::getUser()->id,
                'event' => 'reopened',
                'comment' => $validated['review_comment'],
                'occurred_at' => now(),
            ]);
        });

        return $this->reviewResponse(
            $testingIssue,
            'reopened',
            __('testing_issues.reopened_successfully')
        );
    }

    private function reviewResponse(
        ApplicationTestingDetail $testingIssue,
        string $reviewAction,
        string $successMessage
    ) {
        $testingIssue->loadMissing([
            'applicationTesting.application',
            'unit',
            'fixedBy',
            'reviewedBy',
        ]);

        [$sentCount, $failedCount] = $this->notifyAssignedWorkers(
            $testingIssue,
            $reviewAction
        );

        $response = back()->with('success', $successMessage);

        if ($sentCount === 0) {
            return $response->with(
                'warning',
                __('testing_issues.worker_notification_failed')
            );
        }

        if ($failedCount > 0) {
            return $response->with(
                'warning',
                __('testing_issues.worker_notification_partial', [
                    'sent' => $sentCount,
                    'failed' => $failedCount,
                ])
            );
        }

        return $response->with(
            'mail_success',
            trans_choice(
                'testing_issues.workers_notified',
                $sentCount,
                ['count' => $sentCount]
            )
        );
    }

    private function notifyAssignedWorkers(
        ApplicationTestingDetail $testingIssue,
        string $reviewAction
    ): array {
        $application = $testingIssue->applicationTesting?->application;

        if (!$application || !$testingIssue->fixed_by) {
            return [0, 0];
        }

        $workers = $application->users()
            ->where('users.id', $testingIssue->fixed_by)
            ->whereHas('accessLevels', fn ($query) => $query->where(
                'access_levels.name',
                'Documentation Worker'
            ))
            ->get(['users.id', 'first_name', 'last_name', 'email'])
            ->filter(fn ($worker) =>
                $worker->email &&
                filter_var($worker->email, FILTER_VALIDATE_EMAIL))
            ->unique('email')
            ->values();

        $sentCount = 0;
        $failedCount = 0;

        foreach ($workers as $worker) {
            try {
                Mail::to($worker->email)->send(
                    (new TestingIssueReviewed(
                        $testingIssue,
                        $worker,
                        $reviewAction
                    ))->locale(app()->getLocale())
                );
                $sentCount++;
            } catch (Throwable $exception) {
                $failedCount++;
                Log::error('Testing correction feedback notification failed', [
                    'testing_detail_id' => $testingIssue->id,
                    'review_action' => $reviewAction,
                    'worker_user_id' => $worker->id,
                    'worker_email' => $worker->email,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        Log::info('Testing correction reviewed by administrator', [
            'testing_detail_id' => $testingIssue->id,
            'review_action' => $reviewAction,
            'reviewed_by' => $testingIssue->reviewed_by,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ]);

        return [$sentCount, $failedCount];
    }

    private function notifySuperAdmins(
        ApplicationTestingDetail $testingIssue
    ): array {
        $admins = User::query()
            ->whereHas('roles', fn ($query) => $query->where('roles.slug', 'admin'))
            ->get(['id', 'first_name', 'last_name', 'email'])
            ->filter(fn ($admin) =>
                $admin->email &&
                filter_var($admin->email, FILTER_VALIDATE_EMAIL))
            ->unique('email')
            ->values();

        $sentCount = 0;
        $failedCount = 0;

        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)->send(
                    (new TestingIssueReadyForReview($testingIssue, $admin))
                        ->locale(app()->getLocale())
                );
                $sentCount++;
            } catch (Throwable $exception) {
                $failedCount++;
                Log::error('Testing issue review notification failed', [
                    'testing_detail_id' => $testingIssue->id,
                    'admin_user_id' => $admin->id,
                    'admin_email' => $admin->email,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        Log::info('Testing issue submitted for admin review', [
            'testing_detail_id' => $testingIssue->id,
            'fixed_by' => $testingIssue->fixed_by,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ]);

        return [$sentCount, $failedCount];
    }

    private function authorizeWorkerAssignment(
        ApplicationTestingDetail $testingIssue,
        int $userId
    ): void {
        $application = $testingIssue->applicationTesting?->application;
        abort_unless($application, 404);
        abort_unless(
            $application->users()->where('users.id', $userId)->exists(),
            403,
            __('testing_issues.not_assigned')
        );
    }

    private function isDocumentationWorker($user): bool
    {
        return $user->accessLevels()
            ->where('access_levels.name', 'Documentation Worker')
            ->exists();
    }

    private function authorizeAdmin(): void
    {
        abort_unless(
            Sentinel::check() && Sentinel::inRole('admin'),
            403
        );
    }
}
