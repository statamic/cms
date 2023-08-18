<?php

namespace Statamic\Policies;

use Statamic\Facades\Form;
use Statamic\Facades\User;

class FormPolicy
{
    public function before($user, $ability)
    {
        $user = User::fromUser($user);

        if ($user->hasPermission('configure forms')) {
            return true;
        }
    }

    public function index($user)
    {
        $user = User::fromUser($user);

        if ($this->create($user)) {
            return true;
        }

        return Form::all()
            ->filter(fn ($form) => $this->view($user, $form))
            ->isNotEmpty();
    }

    public function create($user)
    {
        // handled by before()
    }

    public function view($user, $form)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("view {$form->handle()} form submissions");
    }

    public function edit($user, $form)
    {
        // handled by before()
    }

    public function delete($user, $form)
    {
        // handled by before()
    }
}
