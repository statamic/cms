<?php

namespace Statamic\Data\Users\Eloquent;

use Statamic\API\User;
use Illuminate\Support\Facades\DB;

class OAuth
{
    public function getId($user, $provider)
    {
        if ($record = $this->query($user, $provider)->first()) {
            return $record->oauth_id;
        }
    }

    public function setId($user, $provider, $id)
    {
        $this->query($user, $provider)->delete();

        $this->table()->insert([
            'user_id' => $user->id(),
            'provider' => $provider,
            'oauth_id' => $id
        ]);
    }

    public function user($provider, $id)
    {
        $record = $this->table()
            ->where('oauth_id', $id)
            ->where('provider', $provider)
            ->first();

        if ($record) {
            return User::find($record->user_id);
        }
    }

    private function query($user, $provider)
    {
        return $this->table()
            ->where('user_id', $user->id())
            ->where('provider', $provider);
    }

    private function table()
    {
        return DB::table('user_oauth');
    }
}
