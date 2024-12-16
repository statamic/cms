<?php

namespace Statamic\OAuth;

use Closure;
use Illuminate\Support\Arr;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Statamic\Contracts\Auth\User as StatamicUser;
use Statamic\Facades\File;
use Statamic\Facades\User;
use Statamic\Support\Str;

class Provider
{
    protected $userCallback;
    protected $userDataCallback;

    public function __construct(
        protected string $name,
        protected array $config = []
    ) {
    }

    public function getSocialiteUser()
    {
        $driver = Socialite::driver($this->name);

        if (Arr::get($this->config, 'stateless', false)) {
            $driver->stateless();
        }

        return $driver->user();
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
        if ($user = $this->findUser($socialite)) {
            return config('statamic.oauth.merge_user_data', true)
                ? $this->mergeUser($user, $socialite)
                : $user;
        }

        return $this->createUser($socialite);
    }

    /**
     * Find a Statamic user by a Socialite user.
     *
     * @param  SocialiteUser  $socialite
     */
    public function findUser($socialite): ?StatamicUser
    {
        if (
            ($user = User::findByOAuthId($this, $socialite->getId())) ||
            ($user = User::findByEmail($socialite->getEmail()))
        ) {
            return $user;
        }

        return null;
    }

    /**
     * Create a Statamic user from a Socialite user.
     *
     * @param  SocialiteUser  $socialite
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
        collect($this->userData($socialite, $user))->each(fn ($value, $key) => $user->set($key, $value));

        $user->save();

        $this->setUserProviderId($user, $socialite->getId());

        return $user;
    }

    public function userData($socialite, $existingUser = null)
    {
        if ($this->userDataCallback) {
            return call_user_func($this->userDataCallback, $socialite, $existingUser);
        }

        return ['name' => $socialite->getName()];
    }

    public function withUserData(Closure $callback)
    {
        $this->userDataCallback = $callback;

        return $this;
    }

    public function withUser(Closure $callback)
    {
        $this->userCallback = $callback;

        return $this;
    }

    public function loginUrl()
    {
        return route('statamic.oauth.login', $this->name);
    }

    public function label()
    {
        return $this->config['label'] ?? Str::title($this->name);
    }

    public function config()
    {
        return $this->config;
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
