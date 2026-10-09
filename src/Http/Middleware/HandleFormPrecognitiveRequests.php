<?php

namespace Statamic\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;

class HandleFormPrecognitiveRequests extends HandlePrecognitiveRequests
{
    /**
     * Prepare to handle a precognitive request.
     *
     * Unlike Laravel's default behaviour, we don't swap in the precognition
     * dispatcher (which resolves the controller's dependencies and then halts
     * with a 204 before the action runs). The FormController handles
     * precognition itself, so the request must reach the controller.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function prepareForPrecognition($request)
    {
        $request->attributes->set('precognitive', true);
    }
}
