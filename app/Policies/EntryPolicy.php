<?php

namespace Statamic\Policies;

class EntryPolicy
{
    public function index($user)
    {
        //
    }

    public function view($user, $entry)
    {
        return $this->edit($user, $entry)
            || $user->hasPermission("view {$entry->collectionName()} entries");
    }

    public function update($user, $entry)
    {
        return $user->hasPermission("edit {$entry->collectionName()} entries");
    }

    public function create($user, $collection)
    {
        return $user->hasPermission("create {$collection->handle()} entries");
    }

    public function delete($user, $entry)
    {
        //
    }
}
