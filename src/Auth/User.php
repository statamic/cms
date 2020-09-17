<?php

namespace Statamic\Auth;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Password;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Events\UserDeleted;
use Statamic\Events\UserSaved;
use Statamic\Facades;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Value;
use Statamic\Notifications\ActivateAccount as ActivateAccountNotification;
use Statamic\Notifications\PasswordReset as PasswordResetNotification;
use Statamic\Statamic;
use Statamic\Support\Str;

abstract class User implements
    UserContract,
    Authenticatable,
    CanResetPasswordContract,
    Augmentable,
    AuthorizableContract
{
    use Authorizable, Notifiable, CanResetPassword, HasAugmentedInstance, TracksQueriedColumns, HasAvatar;

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
            if (Str::contains($name, ' ')) {
                [$name, $surname] = explode(' ', $name);
            }
        } else {
            $name = $this->email();
        }

        return strtoupper(mb_substr($name, 0, 1).mb_substr($surname, 0, 1));
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
    public function blueprint()
    {
        return Facades\User::blueprint();
    }

    public function save()
    {
        Facades\User::save($this);

        UserSaved::dispatch($this);

        return $this;
    }

    public function delete()
    {
        Facades\User::delete($this);

        UserDeleted::dispatch($this);

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
        $this->notify(new PasswordResetNotification($token));
    }

    public function sendActivateAccountNotification($token)
    {
        $this->notify(new ActivateAccountNotification($token));
    }

    public function generateTokenAndSendPasswordResetNotification()
    {
        $this->sendPasswordResetNotification($this->generatePasswordResetToken());
    }

    public function generateTokenAndSendActivateAccountNotification()
    {
        $this->sendActivateAccountNotification($this->generateActivateAccountToken());
    }

    public function generatePasswordResetToken()
    {
        $broker = config('statamic.users.passwords.'.PasswordReset::BROKER_RESETS);

        return Password::broker($broker)->createToken($this);
    }

    public function generateActivateAccountToken()
    {
        $broker = config('statamic.users.passwords.'.PasswordReset::BROKER_ACTIVATIONS);

        return Password::broker($broker)->createToken($this);
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
