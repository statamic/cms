<?php

namespace Statamic\Auth;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Password;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Facades;
use Statamic\Facades\Blueprint;
use Statamic\Facades\URL;
use Statamic\Fields\Value;
use Statamic\Notifications\ActivateAccount as ActivateAccountNotification;
use Statamic\Notifications\PasswordReset as PasswordResetNotification;
use Statamic\Statamic;

abstract class User implements
    UserContract,
    Authenticatable,
    CanResetPasswordContract,
    Augmentable,
    AuthorizableContract
{
    use Authorizable, Notifiable, CanResetPassword, HasAugmentedInstance, TracksQueriedColumns;

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
            if (Arr::contains($name, ' ')) {
                [$name, $surname] = explode(' ', $name);
            }
        } else {
            $name = $this->email();
        }

        return strtoupper(mb_substr($name, 0, 1).mb_substr($surname, 0, 1));
    }

    public function avatar($size = 64)
    {
        if ($this->hasAvatarField()) {
            return $this->avatarFieldUrl();
        }

        return config('statamic.users.avatars') === 'gravatar'
            ? URL::gravatar($this->email(), $size)
            : null;
    }

    protected function hasAvatarField()
    {
        return $this->has('avatar') && $this->blueprint()->hasField('avatar');
    }

    protected function avatarFieldUrl()
    {
        $value = (new Value($this->get('avatar'), 'avatar', $this->blueprint()->field('avatar')->fieldtype(), $this));

        return $value->value()->url();
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

    public function apiUrl()
    {
        return Statamic::apiRoute('users.show', $this->id());
    }

    public function newAugmentedInstance()
    {
        return new AugmentedUser($this);
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
     * Get or set the blueprint.
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

    public function name()
    {
        if ($name = $this->get('name')) {
            return $name;
        }

        if ($name = $this->get('first_name')) {
            if ($lastName = $this->get('last_name')) {
                $name .= ' '.$lastName;
            }

            return $name;
        }

        return $this->email();
    }

    public function defaultAugmentedArrayKeys()
    {
        return $this->selectedQueryColumns;
    }

    protected function shallowAugmentedArrayKeys()
    {
        return ['id', 'name', 'email', 'api_url'];
    }
}
