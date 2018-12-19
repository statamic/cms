<?php

namespace Statamic\Policies;

use Statamic\API\Form;

class FormSubmissionPolicy
{
    public function before($user, $ability)
    {
        if ($user->hasPermission('configure forms')) {
            return true;
        }
    }

    public function view($user, $submission)
    {
        return $user->hasPermission("view {$submission->form()->handle()} form submissions");
    }

    public function delete($user, $submission)
    {
        return $user->hasPermission("delete {$submission->form()->handle()} form submissions");
    }
}
