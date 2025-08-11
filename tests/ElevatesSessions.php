<?php

namespace Tests;

use Carbon\Carbon;

trait ElevatesSessions
{
    private function withElevatedSession(?Carbon $time = null)
    {
        return $this->session(['statamic_elevated_session' => ($time ?? now())->timestamp]);
    }
}
