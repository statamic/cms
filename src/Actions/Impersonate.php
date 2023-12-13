<?php

namespace Statamic\Actions;

use Illuminate\Events\NullDispatcher;
use Illuminate\Support\Facades\Auth;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\CP\Toast;
use Statamic\Facades\User;

class Impersonate extends Action
{
    protected $confirm = false;

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
            $currentUser = $guard->user();

            $guard->login($users->first());
            session()->put('statamic_impersonated_by', $currentUser->getKey());
            Toast::success(__('You are now impersonating').' '.$users->first()->name());
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
}
