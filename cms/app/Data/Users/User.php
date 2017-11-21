<?php

namespace Statamic\Data\Users;

use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\Hash;
use Statamic\API\YAML;
use Statamic\Data\Data;
use Statamic\API\Config;
use Statamic\API\Fieldset;
use Statamic\Permissions\Permissible;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Statamic\Contracts\Data\Users\User as UserContract;
use Statamic\Contracts\Permissions\Permissible as PermissibleContract;

/**
 * A user
 */
class User extends Data implements UserContract, Authenticatable, PermissibleContract
{
    use Authorizable;
    use Permissible;

    /**
     * Array of OAuth IDs stored in the YAML file
     *
     * @var array
     */
    private static $oauth_ids;

    /**
     * Get or set a user's username
     *
     * @param string|null $username
     * @return mixed
     */
    public function username($username = null)
    {
        if (is_null($username)) {
            return $this->attributes['username'];
        }

        $this->attributes['username'] = $username;
    }

    /**
     * Get or set a user's email address
     *
     * @param string|null $email
     * @return string
     */
    public function email($email = null)
    {
        $login = Config::get('users.login_type');

        if (is_null($email)) {
            return ($login === 'email')
                ? $this->username()
                : $this->get('email');
        }

        if ($login === 'email') {
            $this->username($email);
        } else {
            $this->set('email', $email);
        }
    }

    /**
     * Get or set a user's password
     *
     * @param string|null $password
     * @return string
     */
    public function password($password = null)
    {
        if (is_null($password)) {
            $this->ensureSecured();

            return $this->get('password_hash');
        }

        $this->set('password', $password);
        $this->remove('password_hash');

        $this->securePassword(false);
    }

    /**
     * Get or set the path to the file
     *
     * @param string|null $path
     * @return string
     * @throws \Exception
     */
    public function path($path = null)
    {
        if ($path) {
            throw new \Exception('You cant set the path of a file.');
        }

        if (Config::get('users.login_type') === 'email') {
            if (! $path = $this->email()) {
                throw new \Exception('Cannot get the path of a user without an email.');
            }
        } else {
            if (! $path = $this->username()) {
                throw new \Exception('Cannot get the path of a user without a username.');
            }
        }

        return $path . '.yaml';
    }

    /**
     * Get the path to a localized version
     *
     * @param string $locale
     * @return string
     */
    public function localizedPath($locale)
    {
        // @todo
        dd('todo user@localizedpath');
    }

    /**
     * Save a user to file
     *
     * @return $this
     */
    public function save()
    {
        $content = $this->get('content');
        if ($content || $content == '') {
            $this->remove('content');
        }

        $this->ensureSecured();
        $this->ensureId();

        $data = $this->data();

        if (Config::get('users.login_type') === 'email') {
            unset($data['email']);
        }

        $contents = YAML::dump($data, $content);

        File::disk('users')->put($this->path(), $contents);

        // Has this been renamed?
        if ($this->path() !== $this->originalPath()) {
            File::disk('users')->delete($this->originalPath());
        }

        $this->syncOriginal();

        event('user.saved', $this);

        return $this;
    }
//
//    /**
//     * Delete the data
//     *
//     * @return mixed
//     */
//    public function delete()
//    {
//        File::disk('users')->delete($this->path());
//    }
//
    /**
     * Ensure's this user's password is secured
     *
     * @param bool $save Whether the save after securing
     * @throws \Exception
     */
    public function ensureSecured($save = true)
    {
        // If they don't have a password set, their status is pending.
        // It's not "secured" but there's also nothing *to* secure.
        if ($this->status() == 'pending') {
            return;
        }

        if (! $this->isSecured()) {
            $this->securePassword($save);
        }
    }

    /**
     * Check if the password is secured
     *
     * @return bool
     */
    public function isSecured()
    {
        return (bool) $this->get('password_hash', false);
    }

    /**
     * Secure the password
     *
     * @param bool $save  Whether to save the user
     */
    public function securePassword($save = true)
    {
        if ($this->isSecured()) {
            return;
        }

        if ($password = $this->get('password')) {
            $password = Hash::make($password);

            $this->set('password_hash', $password);
            $this->remove('password');
        }

        if ($save) {
            $this->save();
        }
    }

    /**
     * Get the user's status
     *
     * @return string
     */
    public function status()
    {
        if (! $this->get('password') && ! $this->get('password_hash')) {
            return 'pending';
        }

        return 'active';
    }

    /**
     * Add supplemental data to the attributes
     */
    public function supplement()
    {
        $this->supplements['last_modified'] = File::disk('users')->lastModified($this->path());
        $this->supplements['username'] = $this->username();
        $this->supplements['email'] = $this->email();
        $this->supplements['status'] = $this->status();
        $this->supplements['edit_url'] = $this->editUrl();

        if ($first_name = $this->get('first_name')) {
            $name = $first_name;

            if ($last_name = $this->get('last_name')) {
                $name .= ' ' . $last_name;
            }

            $this->supplements['name'] = $name;
        }

        foreach ($this->roles() as $role) {
            $this->supplements['is_'.Str::slug($role->title(), '_')] = true;
        }

        foreach ($this->groups() as $group) {
            $this->supplements['in_'.Str::slug($group->title(), '_')] = true;
        }

        if ($this->supplement_taxonomies) {
            $this->addTaxonomySupplements();
        }
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->id();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password();
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        $yaml = YAML::parse(File::get($this->rememberPath(), ''));

        return array_get($yaml, $this->id());
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string $value
     * @return void
     */
    public function setRememberToken($token)
    {
        $yaml = YAML::parse(File::get($this->rememberPath(), ''));

        $yaml[$this->id()] = $token;

        File::put($this->rememberPath(), YAML::dump($yaml));
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /**
     * Get the path to the remember me tokens file
     *
     * @return string
     */
    private function rememberPath()
    {
        return cache_path('remember_me.yaml');
    }

    /**
     * Set the reset token/code for a password reset
     *
     * @param  string $token
     * @return void
     */
    public function setPasswordResetToken($token)
    {
        $yaml = YAML::parse(File::get($this->passwordResetPath(), ''));

        $yaml[$this->id()] = $token;

        $yaml = array_filter($yaml);

        File::put($this->passwordResetPath(), YAML::dump($yaml));
    }

    /**
     * Get the reset token/code for a password reset
     *
     * @return string
     */
    public function getPasswordResetToken()
    {
        $yaml = YAML::parse(File::get($this->passwordResetPath(), ''));

        return array_get($yaml, $this->id());
    }

    /**
     * Get the path to the password reset file
     */
    private function passwordResetPath()
    {
        return cache_path('password_resets.yaml');
    }

    /**
     * Get the user's OAuth ID for the requested provider
     *
     * @return string
     */
    public function getOAuthId($provider)
    {
        if (! self::$oauth_ids) {
            self::$oauth_ids = YAML::parse(File::get($this->oAuthIdsPath(), ''));
        }

        return array_get(self::$oauth_ids, $provider.'.'.$this->id());
    }

    /**
     * Set a user's oauth ID
     *
     * @param string $provider
     * @param string $id
     * @return void
     */
    public function setOAuthId($provider, $id)
    {
        $yaml = YAML::parse(File::get($this->oAuthIdsPath(), ''));

        $yaml[$provider][$this->id()] = $id;

        File::put($this->oAuthIdsPath(), YAML::dump($yaml));
    }

    /**
     * Get the path to the oauth IDs file
     *
     * @return string
     */
    private function oAuthIdsPath()
    {
        return cache_path('oauth_ids.yaml');
    }

    /**
     * Get the path before the object was modified.
     *
     * @return string
     * @throws \Exception
     */
    public function originalPath()
    {
        if (! $path = $this->original['attributes']['username']) {
            if (Config::get('users.login_type') === 'email') {
                throw new \Exception('Cannot get the path of a user without an email.');
            } else {
                throw new \Exception('Cannot get the path of a user without a username.');
            }
        }

        return $path . '.yaml';
    }

    /**
     * Get the path to a localized version before the object was modified.
     *
     * @param string $locale
     * @return string
     */
    public function originalLocalizedPath($locale)
    {
        // @todo
        dd('todo: extend data@localizedPath');
    }

    /**
     * Delete the data
     *
     * @return mixed
     */
    public function delete()
    {
        File::disk('users')->delete($this->path());

        // Whoever wants to know about it can do so now.
        $event_class = 'Statamic\Events\Data\UserDeleted';
        event(new $event_class($this->id(), [$this->path()]));
    }

    /**
     * The URL to edit it in the CP
     *
     * @return mixed
     */
    public function editUrl()
    {
        return cp_route('user.edit', $this->username());
    }

    /**
     * Get or set the fieldset
     *
     * @param string|null|bool
     * @return \Statamic\Contracts\CP\Fieldset
     */
    public function fieldset($fieldset = null)
    {
        if (is_null($fieldset)) {
            return Fieldset::get('user');
        }

        $this->set('fieldset', $fieldset);
    }

    /**
     * Whether the data can be taxonomized
     *
     * @return bool
     */
    public function isTaxonomizable()
    {
        return true;
    }
}
