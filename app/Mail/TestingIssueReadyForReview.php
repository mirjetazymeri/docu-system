<?php

namespace App\Mail;

use App\Models\ApplicationTestingDetail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestingIssueReadyForReview extends Mailable
{
    use Queueable, SerializesModels;

    public ApplicationTestingDetail $testingIssue;
    public User $recipient;

    public function __construct(
        ApplicationTestingDetail $testingIssue,
        User $recipient
    ) {
        $this->testingIssue = $testingIssue;
        $this->recipient = $recipient;
    }

    public function build()
    {
        return $this
            ->subject(__('testing_issues.email_subject', [
                'application' => $this->testingIssue
                    ->applicationTesting
                    ->application
                    ->name,
                'reference' => $this->testingIssue->reference,
            ]))
            ->view('emails.testing-issue-ready-for-review');
    }
}
