<?php

namespace Statamic\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Set the successful flash message.
     *
     * @param  string  $message
     * @param  null  $text
     */
    protected function success($message, $text = null)
    {
        session()->flash('success', $message);

        if ($text) {
            session()->flash('success_text', $text);
        }
    }
}
