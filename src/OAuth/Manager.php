<?php

namespace Statamic\OAuth;

use Statamic\Auth\Eloquent\OAuthProvider as EloquentProvider;

class Manager
{
    public function enabled()
    {
        return config('statamic.oauth.enabled')
            && ! empty(config('statamic.oauth.providers'));
    }

    public function provider($provider)
    {
        // Not cached: providers() is cheap (no I/O), and a static, by-name
        // cache here previously meant the first-resolved provider for a
        // given name stuck around for the life of the process (or Octane
        // worker) regardless of later config changes -- the same class of
        // staleness that motivated persisting connections in the database
        // instead of a file in the first place.
        return $this->providers()->get($provider);
    }

    public function providers()
    {
        // Providers persist their connected-account links via getIds()/setIds(),
        // which default to a plain file at storage_path("statamic/oauth/*.php").
        // That's fine for the file user repository, which has no database to
        // lean on -- but it doesn't survive ephemeral or multi-instance
        // deployments, and is inconsistent with the eloquent user repository,
        // where users/roles/groups already live in the database. When the
        // eloquent repository is active, persist connections there too.
        $providerClass = config('statamic.users.repository') === 'eloquent'
            ? EloquentProvider::class
            : Provider::class;

        return collect(config('statamic.oauth.providers'))
            ->mapWithKeys(function ($value, $key) use ($providerClass) {
                $provider = $value;
                $config = [];

                // When the $key is NOT an integer, it means the provider has config settings.
                // eg. ['github' => 'GitHub', 'facebook' => ['label' => 'Facebook', 'stateless' => true]]
                if (! is_int($key)) {
                    $provider = $key;
                    $config = is_array($value) ? $value : ['label' => $value];
                }

                return [$provider => new $providerClass($provider, $config)];
            });
    }
}
