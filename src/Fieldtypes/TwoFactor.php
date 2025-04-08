<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\TwoFactorUser;
use Statamic\Facades\User;
use Statamic\Fields\Fieldtype;

class TwoFactor extends Fieldtype
{
    protected $selectable = false;
    protected $icon = 'key';

    public function preload(): array
    {
        $isEnforced = false;
        $isLocked = false;
        $isSetup = false;
        $isMe = false;
        $routes = [];

        // get the route
        $route = request()->route();
        $user = null;

        // are we on the user edit screen?
        if ($route->getName() === 'statamic.cp.users.edit' || $route->uri() === config('statamic.cp.route').'/users/{user}/edit') {
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

        return [
            'is_enforced' => $isEnforced,
            'is_locked' => $isLocked,
            'is_me' => $isMe,
            'is_setup' => $isSetup,

            'routes' => $routes,
        ];
    }
}
