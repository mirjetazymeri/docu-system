<?php

namespace App\Mail;

use App\Models\ApplicationTestingDetail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestingIssueReviewed extends Mailable
{
    use Queueable, SerializesModels;

    public ApplicationTestingDetail $testingIssue;
    public User $recipient;
    public string $reviewAction;

    public function __construct(
        ApplicationTestingDetail $testingIssue,
        User $recipient,
        string $reviewAction
    ) {
        $this->testingIssue = $testingIssue;
        $this->recipient = $recipient;
        $this->reviewAction = $reviewAction;
    }

    public function build()
    {
        return $this
            ->subject(__('testing_issues.worker_email_subject_' . $this->reviewAction, [
                'application' => $this->testingIssue
                    ->applicationTesting
                    ->application
                    ->name,
                'reference' => $this->testingIssue->reference,
            ]))
            ->view('emails.testing-issue-reviewed');
    }
}
