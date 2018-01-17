<?php

namespace Statamic\Permissions\Eloquent;

use Illuminate\Support\Facades\DB;
use Statamic\Permissions\File\UserGroup as FileUserGroup;

class UserGroup extends FileUserGroup
{
    /**
     * Whether the users have been loaded from the DB.
     *
     * @var boolean
     */
    protected $usersLoaded = false;

    /**
     * Save this group
     *
     * @return mixed
     */
    public function save()
    {
        // Place the users into the DB.
        $this->syncUsers();

        // The rest of the group data continues to go in the file.
        parent::save();
    }

    /**
     * Get the array that should be written to file for this group.
     *
     * @return array
     */
    protected function toSavableArray()
    {
        return tap(parent::toSavableArray(), function (&$arr) {
            unset($arr['users']);
        });
    }

    /**
     * Get or set the users
     *
     * @param array|null $users
     * @return \Statamic\Data\Users\UserCollection
     */
    public function users($users = null)
    {
        if (is_null($users)) {
            $this->loadUsers();
            return parent::users();
        }

        parent::users($users);
    }

    /**
     * Load the users from the DB
     *
     * @return void
     */
    private function loadUsers()
    {
        if ($this->usersLoaded) {
            return;
        }

        $this->users = collect(
            $this->table()->where('group_id', $this->id())->get()
        )->pluck('user_id');

        $this->usersLoaded = true;
    }

    /**
     * Sync up the user/group relationship in the pivot table.
     *
     * @return void
     */
    private function syncUsers()
    {
        $dbUsers = collect(
            $this->table()->where('group_id', $this->id())->get()
        )->keyBy('user_id');

        // Remove users that exist in the DB that should no longer be there.
        foreach ($dbUsers as $user) {
            if (! $this->users->contains($user->user_id)) {
                $this->table()->where('id', $user->id)->delete();
            }
        }

        // Add users that aren't in the DB that should be there.
        foreach ($this->users as $user) {
            if (! $dbUsers->has($user)) {
                $this->table()->insert(['user_id' => $user, 'group_id' => $this->id()]);
            }
        }
    }

    /**
     * Get an instance of the query builder from the appropriate table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function table()
    {
        return DB::table('user_groups');
    }
}
