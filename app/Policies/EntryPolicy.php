<?php

namespace Statamic\Policies;

class EntryPolicy
{
    public function before($user, $ability)
    {
        $user = $user->statamicUser();

        if ($user->hasPermission('configure collections')) {
            return true;
        }
    }

    public function index($user)
    {
        //
    }

    public function view($user, $entry)
    {
        $user = $user->statamicUser();

        return $this->edit($user, $entry)
            || $user->hasPermission("view {$entry->collectionHandle()} entries");
    }

    public function edit($user, $entry)
    {
        $user = $user->statamicUser();

        return $user->hasPermission("edit {$entry->collectionHandle()} entries");
    }

    public function update($user, $entry)
    {
        $user = $user->statamicUser();

        return $this->edit($user, $entry);
    }

    public function create($user, $collection)
    {
        $user = $user->statamicUser();

        return $user->hasPermission("create {$collection->handle()} entries");
    }

    public function store($user, $collection)
    {
        return $this->create($user, $collection);
    }

    public function delete($user, $entry)
    {
        $user = $user->statamicUser();

        return $user->hasPermission("delete {$entry->collectionHandle()} entries");
    }

    public function publish($user, $entry)
    {
        $user = $user->statamicUser();

        return $user->hasPermission("publish {$entry->collectionHandle()} entries");
    }
}
