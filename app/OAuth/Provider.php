<?php

namespace Statamic\OAuth;

use Statamic\API\File;
use Statamic\API\User;
use Statamic\Contracts\Auth\User as StatamicUser;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class Provider
{
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get a Statamic user ID from an OAuth user ID
     *
     * @param string $id  User ID from an OAuth provider
     * @return string|null  A Statamic user ID
     */
    public function getUserId(string $id): ?string
    {
        return array_flip($this->getIds())[$id] ?? null;
    }

    /**
     * Create a Statamic user from a Socialite user
     *
     * @param SocialiteUser $socialite
     * @return StatamicUser
     */
    public function createUser($socialite): StatamicUser
    {
        $user = User::make()
            ->email($socialite->getEmail())
            ->set('name', $socialite->getName());

        $user->save();

        $this->setUserProviderId($user, $socialite->getId());

        return $user;
    }

    protected function getIds()
    {
        if (! File::exists($path = $this->storagePath())) {
            $this->setIds([]);
        }

        return require $path;
    }

    protected function setIds($ids)
    {
        $contents = '<?php return ' . var_export($ids, true) . ';';

        File::put($this->storagePath(), $contents);
    }

    protected function setUserProviderId($user, $id)
    {
        $ids = $this->getIds();

        $ids[$user->id()] = $id;

        $this->setIds($ids);
    }

    protected function storagePath()
    {
        return storage_path("statamic/oauth/{$this->name}.php");
    }
}