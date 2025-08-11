<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Testing\TestResponse;

trait ElevatesSessions
{
    protected function actingAsWithElevatedSession(Authenticatable $user, ?Carbon $time = null)
    {
        return $this->actingAs($user)->withElevatedSession($time);
    }

    protected function withElevatedSession(?Carbon $time = null)
    {
        return $this->session(['statamic_elevated_session' => ($time ?? now())->timestamp]);
    }

    protected function addElevatedSessionMacros()
    {
        TestResponse::macro('assertRedirectToConfirmPasswordForElevatedSession', function () {
            return $this->assertRedirect(route('statamic.cp.confirm-password'));
        });

        TestResponse::macro('assertElevatedSessionRequiredJsonResponse', function () {
            return $this->assertJson(['message' => 'Requires an elevated session.'])->assertStatus(403);
        });
    }
}
