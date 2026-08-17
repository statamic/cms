<?php

namespace Statamic\Policies;

use Statamic\Facades\User;

class FormSubmissionPolicy
{
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
        return User::fromUser($user)->can('viewSubmissions', $submission->form());
    }

    public function markAsNotSpam($user, $submission)
    {
        return User::fromUser($user)->can('viewSubmissions', $submission->form());
    }
}
