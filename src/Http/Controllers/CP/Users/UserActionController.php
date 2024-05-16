<?php

namespace Statamic\Http\Controllers\CP\Users;

use Statamic\Facades\Action;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\ActionController;

class UserActionController extends ActionController
{
    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return User::find($item);
        });
    }

    protected function getItemData($user, $context): array
    {
        $blueprint = $user->blueprint();

        [$values] = $this->extractFromFields($user, $blueprint);

        return [
            'title' => $user->title(),
            'values' => array_merge($values, ['id' => $user->id()]),
            'itemActions' => Action::for($user, $context),
        ];
    }

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
