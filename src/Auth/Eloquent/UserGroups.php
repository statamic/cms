<?php

namespace Statamic\Auth\Eloquent;

use Illuminate\Support\Facades\DB;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\Blink;

class UserGroups
{
    /**
     * @var UserContract
     */
    protected $user;

    /**
     * @param  User  $user
     */
    public function __construct(UserContract $user)
    {
        $this->user = $user;
    }

    /**
     * Get all the group pivot table records for a user.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return Blink::once("eloquent-user-groups-{$this->user->id()}", function () {
            $groups = collect($this->table()->where('user_id', $this->user->id())->get());

            if ($groups->isEmpty()) {
                return collect();
            }

            return $groups;
        });
    }

    /**
     * Sync up the user/group relationship in the pivot table.
     *
     * @param  array  $groups  Array of group IDs the user should belong to.
     * @return void
     */
    public function sync($groups)
    {
        $dbGroups = collect(
            $this->table()->where('user_id', $this->user->id())->get()
        )->keyBy('group_id');

        // Remove groups that exist in the DB that should no longer be there.
        foreach ($dbGroups as $group) {
            if (! $groups->contains($group->group_id)) {
                $this->table()->where('id', $group->id)->delete();
            }
        }

        // Add groups that aren't in the DB that should be there.
        foreach ($groups as $group) {
            if (! $dbGroups->has($group)) {
                $this->table()->insert(['user_id' => $this->user->id(), 'group_id' => $group]);
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
        return DB::connection(config('statamic.users.database'))->table(config('statamic.users.tables.group_user', 'group_user'));
    }
}
