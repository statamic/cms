<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\TwoFactorUser;
use Statamic\Facades\User;
use Statamic\Fields\Fieldtype;

class TwoFactor extends Fieldtype
{
    protected $icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" stroke="currentColor"><path fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="21.34" d="m265.695 325.75-50.97 50.97-7.29 33.2-41.83 2.97-22.39 22.4-2.56 28.45-21.12 8.17-11.2 11.2-84.09 4.64 6.99-81.74 156.5-156.5" /><circle cx="330.135" cy="181.87" r="157.62" fill="none" stroke-miterlimit="10" stroke-width="21.34" /><circle cx="330.135" cy="181.87" r="55.59" fill="none" stroke-miterlimit="10" stroke-width="21.34"/><path fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="21.34" d="m82.105 429.89 136.58-136.57" /></svg>';

    protected $categories = ['special'];

    public function preload(): array
    {
        $isEnabled = config('statamic.users.two_factor.enabled', false);
        $isEnforced = false;
        $isLocked = false;
        $isSetup = false;
        $isMe = false;
        $isUserEdit = false;
        $routes = [];

        // only do this if we are set up
        if ($isEnabled) {
            // get the route
            $route = request()->route();
            $user = null;

            // are we on the user edit screen?
            if ($route->getName() === 'statamic.cp.users.edit' || $route->uri() === config('statamic.cp.route').'/users/{user}/edit') {
                // yep, we are on the user edit view
                $isUserEdit = true;

                // get the user param from the route
                $user_id = $route->parameter('user', null);

                // is it me?
                if ($user_id == User::current()->id) {
                    $isMe = true;
                }

                // load the user
                $user = User::find($user_id);
                if ($user) {
                    if ($user->two_factor_locked) {
                        $isLocked = true;
                    }

                    if ($user?->two_factor_confirmed_at) {
                        $isSetup = true;
                    }
                }

                // build the routes if we have a user
                if ($user) {
                    $routes = [
                        'setup' => null,
                        'locked' => null,
                        'recovery_codes' => [
                            'generate' => null,
                            'show' => null,
                        ],
                        'reset' => cp_route('users.two-factor.reset', ['user' => $user->id]),
                    ];

                    if ($isMe) {
                        // setup
                        $routes['setup'] = cp_route('two-factor.setup');

                        // recovery codes
                        $routes['recovery_codes']['show'] = cp_route('users.two-factor.recovery-codes.show',
                            ['user' => $user->id]);
                        $routes['recovery_codes']['generate'] = cp_route('users.two-factor.recovery-codes.generate',
                            ['user' => $user->id]);
                    }
                    if ($isLocked) {
                        // unlock ability
                        $routes['locked'] = cp_route('users.two-factor.unlock', ['user' => $user->id]);
                    }

                    // are we enforced?
                    $isEnforced = TwoFactorUser::isTwoFactorEnforceable($user);
                }
            }
        }

        return [
            'enabled' => $isEnabled,

            'is_enforced' => $isEnforced,
            'is_locked' => $isLocked,
            'is_me' => $isMe,
            'is_setup' => $isSetup,
            'is_user_edit' => $isUserEdit,

            'routes' => $routes,
        ];
    }
}
