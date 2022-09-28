<?php

namespace Statamic\OAuth;

use Closure;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Statamic\Contracts\Auth\User as StatamicUser;
use Statamic\Facades\File;
use Statamic\Facades\User;
use Statamic\Support\Str;

class Provider
{
    protected $name;
    protected $label;
    protected $userCallback;
    protected $userDataCallback;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get a Statamic user ID from an OAuth user ID.
     *
     * @param  string  $id  User ID from an OAuth provider
     * @return string|null A Statamic user ID
     */
    public function getUserId(string $id): ?string
    {
        return array_flip($this->getIds())[$id] ?? null;
    }

    public function findOrCreateUser($socialite): StatamicUser
    {
        if ($user = User::findByOAuthId($this->name, $socialite->getId())) {
            return $user;
        }

        if ($user = User::findByEmail($socialite->getEmail())) {
            return $this->mergeUser($user, $socialite);
        }

        return $this->createUser($socialite);
    }

    /**
     * Create a Statamic user from a Socialite user.
     *
     * @param  SocialiteUser  $socialite
     * @return StatamicUser
     */
    public function createUser($socialite): StatamicUser
    {
        $user = $this->makeUser($socialite);

        $user->save();

        $this->setUserProviderId($user, $socialite->getId());

        return $user;
    }

    public function makeUser($socialite): StatamicUser
    {
        if ($this->userCallback) {
            return call_user_func($this->userCallback, $socialite);
        }

        return User::make()
            ->email($socialite->getEmail())
            ->data($this->userData($socialite));
    }

    public function mergeUser($user, $socialite): StatamicUser
    {
        collect($this->userData($socialite))->each(fn ($value, $key) => $user->set($key, $value));

        $user->save();

        $this->setUserProviderId($user, $socialite->getId());

        return $user;
    }

    public function userData($socialite)
    {
        if ($this->userDataCallback) {
            return call_user_func($this->userDataCallback, $socialite);
        }

        return ['name' => $socialite->getName()];
    }

    public function withUserData(Closure $callback)
    {
        $this->userDataCallback = $callback;
    }

    public function withUser(Closure $callback)
    {
        $this->userCallback = $callback;
    }

    public function loginUrl()
    {
        return route('statamic.oauth.login', $this->name);
    }

    public function label($label = null)
    {
        if (func_num_args() === 0) {
            return $this->label ?? Str::title($this->name);
        }

        $this->label = $label;

        return $this;
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
        $contents = '<?php return '.var_export($ids, true).';';

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

    public function toArray()
    {
        return [
            'name' => $this->name,
            'label' => $this->label(),
            'loginUrl' => $this->loginUrl(),
        ];
    }
}
