<?php

namespace Statamic\Auth\Eloquent;

use Illuminate\Support\Facades\DB;

/**
 * Persists OAuth provider-account-to-user links in the database.
 *
 * Statamic\OAuth\Provider's default storage writes these to a plain PHP
 * file under storage_path(), which doesn't survive on ephemeral or
 * multi-instance deployments. When the Eloquent user repository is
 * active, links should live in the database the same way users, roles,
 * and groups already do (see OAuthProvider/OAuthManager).
 */
class OAuthConnections
{
    public function __construct(protected string $provider)
    {
    }

    /**
     * Get all provider->user id links for this provider.
     *
     * @return array<string, string> Statamic user id => provider account id
     */
    public function all(): array
    {
        return $this->table()
            ->where('provider', $this->provider)
            ->pluck('provider_user_id', 'user_id')
            ->all();
    }

    /**
     * Replace all links for this provider with the given set.
     *
     * @param  array<string, string>  $ids  Statamic user id => provider account id
     */
    public function sync(array $ids): void
    {
        $this->table()->where('provider', $this->provider)->delete();

        $now = now();

        foreach ($ids as $userId => $providerUserId) {
            $this->table()->insert([
                'provider' => $this->provider,
                'user_id' => $userId,
                'provider_user_id' => $providerUserId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function table()
    {
        return DB::connection(config('statamic.users.database'))
            ->table(config('statamic.users.tables.oauth_connections', 'oauth_connections'));
    }
}
