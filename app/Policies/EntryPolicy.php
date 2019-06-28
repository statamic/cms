<?php

namespace Statamic\Policies;

class EntryPolicy
{
    public function before($user, $ability)
    {
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
        return $this->edit($user, $entry)
            || $user->hasPermission("view {$entry->collectionHandle()} entries");
    }

    public function edit($user, $entry)
    {
        return $user->hasPermission("edit {$entry->collectionHandle()} entries");
    }

    public function update($user, $entry)
    {
        return $this->edit($user, $entry);
    }

    public function create($user, $collection)
    {
        return $user->hasPermission("create {$collection->handle()} entries");
    }

    public function store($user, $collection)
    {
        return $this->create($user, $collection);
    }

    public function delete($user, $entry)
    {
        return $user->hasPermission("delete {$entry->collectionHandle()} entries");
    }

    public function publish($user, $collection)
    {
        return $user->hasPermission("publish {$collection->handle()} entries");
    }
}
