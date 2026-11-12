<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicationTestingRequest;
use App\Mail\ApplicationTestingReport;
use App\Models\Application;
use App\Models\ApplicationTesting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PDF;
use Sentinel;
use Throwable;

class ApplicationTestingController extends Controller
{
    public function index()
    {
        $testings = ApplicationTesting::query()
            ->with('application.users:id,first_name,last_name,email')
            ->withCount([
                'testingDetails',
                'testingDetails as passed_count' => fn ($query) =>
                    $query->where('result', 'passed'),
                'testingDetails as failed_count' => fn ($query) =>
                    $query->where('result', 'not_passed'),
                'testingDetails as in_process_count' => fn ($query) =>
                    $query->where('result', 'in_process'),
                'testingDetails as pending_review_count' => fn ($query) =>
                    $query->where('fix_status', 'pending_review'),
            ])
            ->latest('test_date')
            ->latest('id')
            ->get();

        return view('admin.application_testing.index', compact('testings'));
    }

    public function create()
    {
        return view('admin.application_testing.create', [
            'applications' => Application::query()
                ->orderBy('name')
                ->get(['id', 'name']),
            'units' => collect(),
        ]);
    }

    public function store(ApplicationTestingRequest $request)
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated) {
                $testing = ApplicationTesting::create([
                    'application_id' => $validated['application_id'],
                    'tester_name' => $validated['tester_name'],
                    'version' => $validated['version'],
                    'test_date' => $validated['test_date'],
                ]);

                $testing->testingDetails()->createMany(
                    collect($validated['testing_details'])
                        ->values()
                        ->map(fn (array $detail, int $index) => array_merge(
                            $this->newTestingDetailPayload($detail),
                            ['sequence_number' => $index + 1]
                        ))
                        ->all()
                );
            });

            return redirect()
                ->route('admin.application_testing.index')
                ->with('success', __('ui.testing_record_created_successfully'));
        } catch (Throwable $exception) {
            Log::error('Application testing creation failed', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return back()->withInput()->with(
                'error',
                __('ui.the_testing_record_could_not_be_saved_please_try_again')
            );
        }
    }

    public function edit(ApplicationTesting $application_testing)
    {
        $application_testing->load([
            'testingDetails' => fn ($query) => $query->orderBy('id'),
        ]);

        return view('admin.application_testing.edit', [
            'application_testing' => $application_testing,
            'applications' => Application::query()
                ->orderBy('name')
                ->get(['id', 'name']),
            'units' => Application::findOrFail(
                $application_testing->application_id
            )->units()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(
        ApplicationTestingRequest $request,
        ApplicationTesting $application_testing
    ) {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated, $application_testing) {
                $application_testing->update([
                    'application_id' => $validated['application_id'],
                    'tester_name' => $validated['tester_name'],
                    'version' => $validated['version'],
                    'test_date' => $validated['test_date'],
                ]);

                $keptDetailIds = [];
                $nextSequence = ((int) $application_testing
                    ->testingDetails()
                    ->lockForUpdate()
                    ->max('sequence_number')) + 1;

                foreach ($validated['testing_details'] as $detailData) {
                    $detailId = $detailData['id'] ?? null;
                    unset($detailData['id']);

                    if (!$detailId) {
                        $createdDetail = $application_testing
                            ->testingDetails()
                            ->create(array_merge(
                                $this->newTestingDetailPayload($detailData),
                                ['sequence_number' => $nextSequence]
                            ));
                        $nextSequence++;
                        $keptDetailIds[] = $createdDetail->id;
                        continue;
                    }

                    $detail = $application_testing->testingDetails()
                        ->whereKey($detailId)
                        ->firstOrFail();
                    $previousResult = $detail->result;
                    $previousFixStatus = $detail->fix_status;
                    $payload = $this->baseTestingDetailPayload($detailData);

                    if (!$detail->sequence_number) {
                        $payload['sequence_number'] = $nextSequence;
                        $nextSequence++;
                    }

                    if ($detailData['result'] === 'passed') {
                        $payload = array_merge($payload, [
                            'fix_status' => 'verified',
                            'reviewed_by' => Sentinel::getUser()->id,
                            'reviewed_at' => now(),
                        ]);
                    } elseif ($previousResult !== $detailData['result']) {
                        $payload = array_merge($payload, [
                            'fix_status' => 'open',
                            'reported_by' => null,
                            'reported_at' => null,
                            'fix_comment' => null,
                            'fixed_by' => null,
                            'fixed_at' => null,
                            'reviewed_by' => null,
                            'reviewed_at' => null,
                            'review_comment' => null,
                        ]);
                    }

                    $detail->update($payload);
                    $keptDetailIds[] = $detail->id;

                    if (
                        $detailData['result'] === 'passed' &&
                        ($previousResult !== 'passed' || $previousFixStatus !== 'verified')
                    ) {
                        $detail->activities()->create([
                            'user_id' => Sentinel::getUser()->id,
                            'event' => 'verified',
                            'occurred_at' => now(),
                        ]);
                    }
                }

                $application_testing->testingDetails()
                    ->whereNotIn('id', $keptDetailIds)
                    ->delete();
            });

            return redirect()
                ->route('admin.application_testing.index')
                ->with('success', __('ui.testing_record_updated_successfully'));
        } catch (Throwable $exception) {
            Log::error('Application testing update failed', [
                'testing_id' => $application_testing->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return back()->withInput()->with(
                'error',
                __('ui.changes_could_not_be_saved_previous_data_has_been_preserved')
            );
        }
    }

    public function destroy(ApplicationTesting $application_testing)
    {
        $application_testing->delete();

        return redirect()
            ->route('admin.application_testing.index')
            ->with('success', __('ui.testing_record_deleted_successfully'));
    }

    public function updateCompletion(
        Request $request,
        ApplicationTesting $application_testing
    ) {
        abort_unless(Sentinel::check() && Sentinel::inRole('admin'), 403);

        $validated = $request->validate([
            'is_completed' => ['required', 'boolean'],
        ]);
        $isCompleted = (bool) $validated['is_completed'];

        $application_testing->update([
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? now() : null,
            'completed_by' => $isCompleted ? Sentinel::getUser()->id : null,
        ]);

        return back()->with(
            'success',
            $isCompleted
                ? __('application_status.testing_marked_completed')
                : __('application_status.testing_marked_incomplete')
        );
    }

    public function getUnits(int $applicationId)
    {
        $application = Application::find($applicationId);

        if (!$application) {
            return response()->json(['units' => []], 404);
        }

        return response()->json([
            'units' => $application->units()
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    /*
     * PDF logic intentionally remains unchanged for the next design phase.
     */
    public function generatePdf(ApplicationTesting $application_testing)
    {
        $application_testing->loadMissing([
            'application:id,name',
            'application.users:id,first_name,last_name',
            'testingDetails.unit:id,name',
        ]);

        $projectName = preg_replace(
            '/[^A-Za-z0-9 _-]+/',
            '',
            Str::ascii($application_testing->application->name)
        );
        $projectName = trim(preg_replace('/\s+/', ' ', $projectName));
        $filename = ($projectName ?: 'Application') . '-Testing Report.pdf';

        if ($application_testing->pdf_url) {
            $filePath = storage_path(
                'app/' . str_replace('storage/', '', $application_testing->pdf_url)
            );

            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);
        }

        $pdf = PDF::loadView('admin.application_testing.pdf_report', [
            'testing' => $application_testing,
            'testingDetails' => $application_testing->testingDetails,
        ]);

        return $pdf->stream($filename);
    }

    public function sendReport(ApplicationTesting $application_testing)
    {
        abort_unless(
            Sentinel::check() && Sentinel::inRole('admin'),
            403
        );

        $application_testing->loadMissing([
            'application.users:id,first_name,last_name,email',
            'testingDetails.unit:id,name',
        ]);

        if (!$application_testing->application) {
            return back()->with(
                'error',
                __('application_testing_report.application_missing')
            );
        }

        $recipients = $application_testing->application->users
            ->filter(
                fn ($user) =>
                    $user->email &&
                    filter_var($user->email, FILTER_VALIDATE_EMAIL)
            )
            ->unique('email')
            ->values();

        if ($recipients->isEmpty()) {
            return back()->with(
                'error',
                __('application_testing_report.no_recipients')
            );
        }

        $actionItems = $application_testing->testingDetails
            ->whereIn('result', ['not_passed', 'in_process'])
            ->values();

        if ($actionItems->isEmpty()) {
            return back()->with(
                'warning',
                __('application_testing_report.no_action_items')
            );
        }

        $sentCount = 0;
        $failedCount = 0;

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(
                    (new ApplicationTestingReport(
                        $application_testing,
                        $recipient,
                        $actionItems
                    ))->locale(app()->getLocale())
                );

                $sentCount++;
            } catch (Throwable $exception) {
                $failedCount++;

                Log::error('Application testing report email failed', [
                    'testing_id' => $application_testing->id,
                    'application_id' => $application_testing->application_id,
                    'recipient_user_id' => $recipient->id,
                    'recipient_email' => $recipient->email,
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ]);
            }
        }

        if ($sentCount === 0) {
            return back()->with(
                'error',
                __('application_testing_report.send_failed')
            );
        }

        DB::transaction(function () use ($actionItems) {
            foreach ($actionItems as $detail) {
                $detail->update([
                    'reported_by' => Sentinel::getUser()->id,
                    'reported_at' => now(),
                ]);

                $detail->activities()->create([
                    'user_id' => Sentinel::getUser()->id,
                    'event' => 'reported_to_worker',
                    'occurred_at' => now(),
                ]);
            }
        });

        if ($failedCount > 0) {
            Log::warning('Application testing report partially sent', [
                'testing_id' => $application_testing->id,
                'application_id' => $application_testing->application_id,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
            ]);

            return back()
                ->with(
                    'success',
                    trans_choice(
                        'application_testing_report.sent_success',
                        $sentCount,
                        ['count' => $sentCount]
                    )
                )
                ->with(
                    'warning',
                    trans_choice(
                        'application_testing_report.partial_failure',
                        $failedCount,
                        [
                            'sent' => $sentCount,
                            'failed' => $failedCount,
                        ]
                    )
                );
        }

        Log::info('Application testing report sent', [
            'testing_id' => $application_testing->id,
            'application_id' => $application_testing->application_id,
            'recipients_count' => $sentCount,
            'action_items_count' => $actionItems->count(),
        ]);

        return back()->with(
            'success',
            trans_choice(
                'application_testing_report.sent_success',
                $sentCount,
                ['count' => $sentCount]
            )
        );
    }

    private function baseTestingDetailPayload(array $detail): array
    {
        return [
            'unit_id' => $detail['unit_id'],
            'test_case' => $detail['test_case'],
            'result' => $detail['result'],
            'comment' => $detail['comment'] ?? null,
            'category' => $detail['category'],
        ];
    }

    private function newTestingDetailPayload(array $detail): array
    {
        return array_merge(
            $this->baseTestingDetailPayload($detail),
            [
                'fix_status' => $detail['result'] === 'passed'
                    ? 'verified'
                    : 'open',
            ]
        );
    }
}
