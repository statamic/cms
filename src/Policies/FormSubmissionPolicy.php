<?php

namespace Statamic\Policies;

use Statamic\Facades\User;

class FormSubmissionPolicy
{
    /**
     * @deprecated FormPolicy::before() already covers super users and the "configure forms" permission.
     */
    public function before($user, $ability)
    {
        $user = User::fromUser($user);

        if ($user->isSuper() || $user->hasPermission('configure forms')) {
            return true;
        }
    }

    public function view($user, $submission)
    {
        return User::fromUser($user)->can('viewSubmissions', $submission->form());
    }

    public function delete($user, $submission)
    {
        return User::fromUser($user)->can('deleteSubmissions', $submission->form());
    }

    public function markAsSpam($user, $submission)
    {
        return User::fromUser($user)->can('manageSpam', $submission->form());
    }

    public function markAsNotSpam($user, $submission)
    {
        return User::fromUser($user)->can('manageSpam', $submission->form());
    }
}
