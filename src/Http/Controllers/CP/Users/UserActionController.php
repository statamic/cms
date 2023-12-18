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

    protected function getItemData($user, $context)
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
        $fields = $blueprint
            ->fields()
            ->addValues(array_merge(
                $user->data()->all(),
                ['email' => $user->email()],
            ))
            ->preProcess();

        return [$fields->values()->all(), $fields->meta()->all()];
    }
}
