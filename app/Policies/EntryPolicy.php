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
        //
    }

    public function edit($user, $entry)
    {
        return $user->hasPermission("edit {$entry->collectionName()} entries");
    }

    public function update($user, $entry)
    {
        //
    }

    public function create($user)
    {
        //
    }

    public function store($user)
    {
        //
    }

    public function delete($user, $entry)
    {
        //
    }
}
