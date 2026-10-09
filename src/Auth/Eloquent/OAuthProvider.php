<?php

namespace Statamic\Auth\Eloquent;

use Statamic\OAuth\Provider;

/**
 * Persists OAuth provider links via OAuthConnections (database) instead
 * of Provider's default storage_path() file.
 */
class OAuthProvider extends Provider
{
    protected function getIds()
    {
        return (new OAuthConnections($this->name))->all();
    }

    protected function setIds($ids)
    {
        (new OAuthConnections($this->name))->sync($ids);
    }
}
