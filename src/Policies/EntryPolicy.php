<?php

namespace Statamic\Policies;

use Statamic\Facades\User;

class EntryPolicy
{
    public function before($user, $ability)
    {
        $user = User::fromUser($user);

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
        $user = User::fromUser($user);

        return $this->edit($user, $entry)
            || $user->hasPermission("view {$entry->collectionHandle()} entries");
    }

    public function edit($user, $entry)
    {
        $user = User::fromUser($user);

        if ($this->hasAnotherAuthor($user, $entry)) {
            return $user->hasPermission("edit other authors {$entry->collectionHandle()} entries");
        }

        return $user->hasPermission("edit {$entry->collectionHandle()} entries");
    }

    public function editOtherAuthorsEntries($user, $collection, $blueprint = null)
    {
        $blueprint = $blueprint ?? $collection->entryBlueprint();

        if ($blueprint->hasField('author') === false) {
            return true;
        }

        return $user->hasPermission("edit other authors {$collection->handle()} entries");
    }

    public function update($user, $entry)
    {
        $user = User::fromUser($user);

        return $this->edit($user, $entry);
    }

    public function create($user, $collection)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("create {$collection->handle()} entries");
    }

    public function store($user, $collection)
    {
        return $this->create($user, $collection);
    }

    public function delete($user, $entry)
    {
        $user = User::fromUser($user);

        if ($this->hasAnotherAuthor($user, $entry)) {
            return $user->hasPermission("delete other authors {$entry->collectionHandle()} entries");
        }

        return $user->hasPermission("delete {$entry->collectionHandle()} entries");
    }

    public function publish($user, $entry)
    {
        $user = User::fromUser($user);

        if ($this->hasAnotherAuthor($user, $entry)) {
            return $user->hasPermission("publish other authors {$entry->collectionHandle()} entries");
        }

        return $user->hasPermission("publish {$entry->collectionHandle()} entries");
    }

    protected function hasAnotherAuthor($user, $entry)
    {
        if ($entry->blueprint()->hasField('author') === false) {
            return false;
        }

        return ! $entry->authors()->contains($user->id());
    }
}
