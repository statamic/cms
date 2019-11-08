<?php

namespace Statamic\Auth;

use ArrayAccess;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Password;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Data\Augmentable as AugmentableContract;
use Statamic\Data\Augmentable;
use Statamic\Facades;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Preference;
use Statamic\Facades\URL;
use Statamic\Notifications\ActivateAccount as ActivateAccountNotification;
use Statamic\Notifications\PasswordReset as PasswordResetNotification;
use Statamic\Support\Arr;

abstract class User implements UserContract, Authenticatable, CanResetPasswordContract, AugmentableContract, Arrayable, ArrayAccess
{
    use Authorizable, Notifiable, CanResetPassword, Augmentable;

    abstract public function get($key, $fallback = null);
    abstract public function value($key);
    abstract public function has($key);
    abstract public function set($key, $value);
    abstract public function remove($key);

    public function reference()
    {
        return "user::{$this->id()}";
    }

    public function title()
    {
        return $this->email();
    }

    public function initials()
    {
        $surname = '';
        if ($name = $this->get('name')) {
            if (str_contains($name, ' ')) {
                list($name, $surname) = explode(' ', $name);
            }
        } else {
            $name = $this->email();
        }

        return strtoupper(substr($name, 0, 1) . substr($surname, 0, 1));
    }

    public function augmentedArrayData()
    {
        return $this->data();
    }

    public function avatar($size = 64)
    {
        if ($this->blueprint()->hasField('avatar') && $this->has('avatar') && $this->augment('avatar')->value()) {
            return $this->augment('avatar')->value()->url();
        }

        return config('statamic.users.avatars') === 'gravatar'
            ? URL::gravatar($this->email(), $size)
            : null;
    }

    public function isSuper()
    {
        if ((bool) $this->get('super')) {
            return true;
        }

        return $this->hasPermission('super');
    }

    public function isTaxonomizable()
    {
        return true;
    }

    public function editUrl()
    {
        return cp_route('users.edit', $this->id());
    }

    public function updateUrl()
    {
        return cp_route('users.update', $this->id());
    }

    public function toArray()
    {
        $roles = $this->roles()->mapWithKeys(function ($role) {
            return ["is_{$role->handle()}" => true];
        })->all();

        $groups = $this->groups()->mapWithKeys(function ($group) {
            return ["in_{$group->handle()}" => true];
        })->all();

        return $this->data()->merge([
            'name' => $this->name(),
            'id' => $this->id(),
            'title' => $this->title(),
            'email' => $this->email(),
            'avatar' => $this->avatar(),
            'initials' => $this->initials(),
            'preferences' => Preference::all(), // Preference API respects fallbacks to role preferences!
            'permissions' => $this->permissions()->all(),
            'edit_url' => $this->editUrl(),
            'is_user' => true,
            'last_login' => $this->lastLogin(),
        ])->merge($roles)->merge($groups)->merge($this->supplements)->all();
    }

    public function getAuthIdentifierName()
    {
        //
    }

    public function getAuthIdentifier()
    {
        return $this->id();
    }

    public function getAuthPassword()
    {
        return $this->password();
    }

    /**
     * Get or set the blueprint
     *
     * @param string|null|bool
     * @return \Statamic\Fields\Blueprint
     */
    public function blueprint($blueprint = null)
    {
        if (is_null($blueprint)) {
            return Blueprint::find('user');
        }

        $this->set('blueprint', $blueprint);
    }

    public function save()
    {
        Facades\User::save($this);

        // TODO: dispatch event

        return $this;
    }

    public function delete()
    {
        Facades\User::delete($this);

        // TODO: dispatch event

        return $this;
    }

    public function getEmailForPasswordReset()
    {
        return $this->email();
    }

    public function routeNotificationForMail($notification = null)
    {
        return $this->email();
    }

    public function sendPasswordResetNotification($token)
    {
        $notification = $this->password()
            ? new PasswordResetNotification($token)
            : new ActivateAccountNotification($token);

        $this->notify($notification);
    }

    public function generateTokenAndSendPasswordResetNotification()
    {
        $this->sendPasswordResetNotification($this->generatePasswordResetToken());
    }

    public function getPasswordResetUrl()
    {
        return PasswordReset::url($this->generatePasswordResetToken());
    }

    public function generatePasswordResetToken()
    {
        return Password::broker()->createToken($this);
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\User::{$method}(...$parameters);
    }

    public function offsetExists($key)
    {
        return $this->has($key);
    }

    public function offsetGet($key)
    {
        return $this->value($key);
    }

    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    public function name()
    {
        if ($name = $this->get('name')) {
            return $name;
        }

        if ($name = $this->get('first_name')) {
            if ($lastName = $this->get('last_name')) {
                $name .= ' ' . $lastName;
            }

            return $name;
        }

        return $this->email();
    }
}
