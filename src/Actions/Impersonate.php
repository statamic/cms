<?php

namespace Statamic\Actions;

use Illuminate\Events\NullDispatcher;
use Illuminate\Support\Facades\Auth;
use Statamic\Contracts\Auth\User as UserContract;
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
        if (session()->get('statamic_impersonated_by')) {
            return false;
        }

        return $item instanceof UserContract && $item != User::current();
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
            $guard->login($users->first());
            session()->put('statamic_impersonated_by', $guard->user()->getKey());
        } finally {
            if ($dispatcher) {
                $guard->setDispatcher($dispatcher);
            }
        }
    }

    public function redirect($users, $values)
    {
        if ($url = config('statamic.users.impersonate_redirect')) {
            return $url;
        }

        return $users->first()->can('access cp') ? cp_route('dashboard') : '/';
    }
}
