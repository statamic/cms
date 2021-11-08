<?php

namespace Statamic\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Test extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        if ($queue = config('statamic.system.queue')) {
            $this->onQueue($queue);
        }
        if ($connection = config('statamic.system.queue_connection')) {
            $this->onConnection($connection);
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('statamic::email.test');
    }
}
