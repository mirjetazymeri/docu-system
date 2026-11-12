<?php

namespace App\Mail;

use App\Models\ApplicationTesting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ApplicationTestingReport extends Mailable
{
    use Queueable, SerializesModels;

    public ApplicationTesting $testing;
    public User $recipient;
    public Collection $actionItems;

    public function __construct(
        ApplicationTesting $testing,
        User $recipient,
        Collection $actionItems
    ) {
        $this->testing = $testing;
        $this->recipient = $recipient;
        $this->actionItems = $actionItems;
    }

    public function build()
    {
        return $this
            ->subject(__('application_testing_report.email_subject', [
                'application' => $this->testing->application->name,
            ]))
            ->view('emails.application-testing-report');
    }
}
