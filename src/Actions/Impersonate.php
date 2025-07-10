<?php

namespace Statamic\Actions;

use Illuminate\Events\NullDispatcher;
use Illuminate\Support\Facades\Auth;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Events\ImpersonationStarted;
use Statamic\Facades\CP\Toast;
use Statamic\Facades\User;

class Impersonate extends Action
{
    public $icon = 'mask';

    public static function title()
    {
        return __('Start Impersonating');
    }

    public function visibleTo($item)
    {
        if (! config('statamic.users.impersonate.enabled', true) || session()->get('statamic_impersonated_by')) {
            return false;
        }

        return $item instanceof UserContract && $item->id() != User::current()->id();
    }

    public function visibleToBulk($items)
    {
        return false;
    }

    public function authorize($authed, $user)
    {
        return $authed->can('impersonate users');
    }

    public function run($users, $values)
    {
        $guard = Auth::guard();

        $dispatcher = $guard->getDispatcher();

        if ($dispatcher) {
            $guard->setDispatcher(new NullDispatcher($dispatcher));
        }

        try {
            $impersonator = $guard->user();
            $impersonated = $users->first();

            $guard->login($users->first());
            session()->put('statamic_impersonated_by', $impersonator->getKey());
            Toast::success(__('You are now impersonating').' '.$impersonated->name());

            ImpersonationStarted::dispatch($impersonator, $impersonated);
        } finally {
            if ($dispatcher) {
                $guard->setDispatcher($dispatcher);
            }
        }
    }

    public function redirect($users, $values)
    {
        if ($url = config('statamic.users.impersonate.redirect')) {
            return $url;
        }

        return $users->first()->can('access cp') ? cp_route('index') : '/';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'statamic::messages.impersonate_action_confirmation';
    }

    public function buttonText()
    {
        /** @translation */
        return 'Confirm';
    }

    public function bypassesDirtyWarning(): bool
    {
        return true;
    }

    public function requiresElevatedSession(): bool
    {
        return true;
    }
}
