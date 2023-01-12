<?php

namespace Statamic\Forms;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Statamic\Contracts\Forms\Submission;
use Statamic\Sites\Site;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $submission;
    public $site;
    public $config;

    public function __construct(Submission $submission, Site $site, $config)
    {
        $this->submission = $submission;
        $this->site = $site;
        $this->config = $config;
    }

    public function handle()
    {
        Mail::send(new Email($this->submission, $this->config, $this->site));
    }
}
