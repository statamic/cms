<?php

namespace Statamic\Http\Controllers\CP\Users;

use Statamic\Facades\Action;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\ActionController;

class UserActionController extends ActionController
{
    use ExtractsFromUserFields;

    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return User::find($item);
        });
    }

    protected function getItemData($user, $context): array
    {
        $blueprint = $user->blueprint();

        [$values, $meta] = $this->extractFromFields($user, $blueprint);

        return [
            'title' => $user->title(),
            'values' => array_merge($values, ['id' => $user->id()]),
            'meta' => $meta,
            'itemActions' => Action::for($user, $context),
        ];
    }
}
