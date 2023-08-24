<?php

namespace Statamic\Actions;

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

        session()->put('statamic_impersonated_by', $guard->user()->getKey());
        $guard->login($users->first());
    }

    public function redirect($users, $values)
    {
        if ($url = config('statamic.users.impersonate_redirect')) {
            return $url;
        }

        return $users->first()->can('access cp') ? cp_route('dashboard') : '/';
    }
}
