<?php

namespace Statamic\Http\Controllers\CP\Forms\Concerns;

use Statamic\Contracts\Forms\Form;
use Statamic\Facades\User;

trait ProvidesFormAbilities
{
    private function formAbilities(Form $form): array
    {
        $user = User::current();

        return [
            'edit' => $user->can('edit', $form),
            'delete' => $user->can('delete', $form),
            'editFields' => $user->can('editFields', $form),
            'viewSubmissions' => $user->can('viewSubmissions', $form),
            'generateFakeSubmissions' => $user->can('generateFakeSubmissions', $form),
        ];
    }
}
