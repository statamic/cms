<?php

namespace Statamic\Policies;

use Statamic\Facades\GlobalSet;
use Statamic\Facades\User;

class GlobalSetPolicy
{
    use Concerns\HasMultisitePolicy;

    public function before($user, $ability, $set)
    {
        $user = User::fromUser($user);

        if ($user->hasPermission('configure globals')) {
            return true;
        }

        if ($this->siteIsForbidden($user, $set)) {
            return false;
        }
    }

    public function index($user)
    {
        $user = User::fromUser($user);

        if ($this->create($user)) {
            return true;
        }

        return GlobalSet::all()
            ->filter(fn ($set) => $this->view($user, $set))
            ->isNotEmpty();
    }

    public function create($user)
    {
        // handled by before()
    }

    public function view($user, $set)
    {
        return $this->edit($user, $set);
    }

    public function edit($user, $set)
    {
        return User::fromUser($user)->hasPermission("edit {$set->handle()} globals");
    }

    public function configure($user, $set)
    {
        return User::fromUser($user)->hasPermission('configure globals');
    }

    public function delete($user, $set)
    {
        // handled by before()
    }
}
