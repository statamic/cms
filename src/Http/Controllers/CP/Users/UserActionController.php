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

        [$values, $meta] = $this->extractFromFields($user, $blueprint);

        return [
            'itemActions' => Action::for($user, $context),
            'values' => array_merge($values, ['id' => $user->id()]),
            'meta' => $meta,
        ];
    }

    protected function extractFromFields($user, $blueprint)
    {
        $fields = $blueprint
            ->fields()
            ->addValues($user->data()->all())
            ->preProcess();

        return [$fields->values()->all(), $fields->meta()->all()];
    }
}
