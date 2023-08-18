<?php

namespace Statamic\Policies;

use Statamic\Facades\Nav;
use Statamic\Facades\User;

class NavPolicy
{
    use Concerns\HasMultisitePolicy;

    public function before($user, $ability, $nav)
    {
        $user = User::fromUser($user);

        if ($user->hasPermission('configure navs')) {
            return true;
        }

        if ($this->siteIsForbidden($user, $nav)) {
            return false;
        }
    }

    public function index($user)
    {
        $user = User::fromUser($user);

        if ($this->create($user)) {
            return true;
        }

        return Nav::all()
            ->filter(fn ($nav) => $this->view($user, $nav))
            ->isNotEmpty();
    }

    public function create($user)
    {
        // handled by before()
    }

    public function store($user)
    {
        // handled by before()
    }

    public function view($user, $nav)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("view {$nav->handle()} nav");
    }

    public function edit($user, $nav)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("edit {$nav->handle()} nav");
    }

    public function update($user, $nav)
    {
        return $this->edit($user, $nav);
    }

    public function delete($user, $nav)
    {
        // handled by before()
    }
}
