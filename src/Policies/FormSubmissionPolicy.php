<?php

namespace Statamic\Policies;

use Statamic\Facades\User;

class FormSubmissionPolicy
{
    public function before($user, $ability)
    {
        $user = User::fromUser($user);

        if ($user->hasPermission('configure forms')) {
            return true;
        }
    }

    public function view($user, $submission)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("view {$submission->form()->handle()} form submissions");
    }

    public function delete($user, $submission)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("delete {$submission->form()->handle()} form submissions");
    }
}
