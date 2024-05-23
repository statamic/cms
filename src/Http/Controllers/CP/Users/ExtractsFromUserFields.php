<?php

namespace Statamic\Http\Controllers\CP\Users;

trait ExtractsFromUserFields
{
    protected function extractFromFields($user, $blueprint)
    {
        $values = $user->data()
            ->merge($user->computedData())
            ->merge(['email' => $user->email()]);

        $fields = $blueprint
            ->removeField('password')
            ->removeField('password_confirmation')
            ->fields()
            ->addValues($values->all())
            ->preProcess();

        return [$fields->values()->all(), $fields->meta()->all()];
    }
}
