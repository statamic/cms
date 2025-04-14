<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\User;
use Statamic\Fields\Fieldtype;

class TwoFactor extends Fieldtype
{
    protected $selectable = false;
    protected $icon = 'key';

    public function preload(): array
    {
        $route = request()->route();

        if ($route->uri() !== config('statamic.cp.route').'/users/{user}/edit') {
            return [];
        }

        $user = User::find($route->parameter('user'));

        return [
            'is_current_user' => $user->id === User::current()->id,
            'is_enforced' => $user->isTwoFactorAuthRequired(),
            'is_setup' => ! is_null($user->two_factor_confirmed_at),
            'routes' => [
                'setup' => cp_route('two-factor.setup'),
                'disable' => cp_route('users.two-factor.disable', $user->id),
                'recovery_codes' => [
                    'show' => cp_route('users.two-factor.recovery-codes.show', $user->id),
                    'generate' => cp_route('users.two-factor.recovery-codes.generate', $user->id),
                    'download' => cp_route('users.two-factor.recovery-codes.download', $user->id),
                ],
            ],
        ];
    }

    public function preProcessIndex($data)
    {
        $user = $this->field->parent();

        return [
            'setup' => ! is_null($user->two_factor_confirmed_at),
        ];
    }
}
