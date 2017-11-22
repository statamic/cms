<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\API\User;

class PublishUserController extends PublishController
{
    /**
     * Build the redirect.
     *
     * @param  Request  $request
     * @param  \Statamic\Contracts\Data\Users\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirect(Request $request, $user)
    {
        $currentUser = User::getCurrent();
        $edit = route('user.edit', $user->username());
        $index = route('users');

        if ($request->continue) {
            return $edit;
        }

        if ($currentUser->hasPermission('users:edit')) {
            return $index;
        }

        return $edit;
    }

    /**
     * Whether the user is authorized to publish the object.
     *
     * @param Request $request
     * @return bool
     */
    protected function canPublish(Request $request)
    {
        $user = User::find($request->uuid);
        $currentUser = User::getCurrent();

        return $currentUser === $user || $currentUser->hasPermission('users:edit');
    }
}
