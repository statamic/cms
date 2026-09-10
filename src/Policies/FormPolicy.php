<?php

namespace Statamic\Policies;

use Statamic\Facades\Form;
use Statamic\Facades\User;

class FormPolicy
{
    public function before($user, $ability)
    {
        $user = User::fromUser($user);

        if ($user->isSuper() || $user->hasPermission('configure forms')) {
            return true;
        }
    }

    public function index($user)
    {
        $user = User::fromUser($user);

        if ($this->create($user)) {
            return true;
        }

        return ! Form::all()->filter(function ($form) use ($user) {
            return $this->view($user, $form);
        })->isEmpty();
    }

    public function create($user)
    {
        $user = User::fromUser($user);

        return $user->hasPermission('create forms');
    }

    public function view($user, $form)
    {
        $user = User::fromUser($user);

        return $this->editFields($user, $form)
            || $this->viewSubmissions($user, $form);
    }

    public function edit($user, $form)
    {
        $user = User::fromUser($user);

        return $user->hasPermission('edit forms')
            || $user->hasPermission("edit {$form->handle()} form");
    }

    public function delete($user, $form)
    {
        $user = User::fromUser($user);

        return $user->hasPermission('delete forms');
    }

    public function editFields($user, $form)
    {
        $user = User::fromUser($user);

        return $this->edit($user, $form)
            || $user->hasPermission('configure form fields');
    }

    public function viewSubmissions($user, $form)
    {
        $user = User::fromUser($user);

        return $user->hasPermission('view form submissions')
            || $user->hasPermission("view {$form->handle()} form submissions");
    }

    public function deleteSubmissions($user, $form)
    {
        $user = User::fromUser($user);

        return $user->hasPermission('delete form submissions')
            || $user->hasPermission("delete {$form->handle()} form submissions");
    }

    public function generateFakeSubmissions($user, $form)
    {
        // handled by before()
    }
}
