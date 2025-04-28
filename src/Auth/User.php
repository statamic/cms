<?php

namespace Statamic\Auth;

use ArrayAccess;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Password;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Contracts\Auth\Role as RoleContract;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Contracts\GraphQL\ResolvesValues as ResolvesValuesContract;
use Statamic\Contracts\Query\ContainsQueryableValues;
use Statamic\Contracts\Search\Searchable as SearchableContract;
use Statamic\Data\ContainsComputedData;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Data\HasDirtyState;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Data\TracksQueriedRelations;
use Statamic\Events\UserCreated;
use Statamic\Events\UserCreating;
use Statamic\Events\UserDeleted;
use Statamic\Events\UserDeleting;
use Statamic\Events\UserSaved;
use Statamic\Events\UserSaving;
use Statamic\Facades;
use Statamic\GraphQL\ResolvesValues;
use Statamic\Notifications\ActivateAccount as ActivateAccountNotification;
use Statamic\Notifications\PasswordReset as PasswordResetNotification;
use Statamic\Search\Searchable;
use Statamic\Statamic;
use Statamic\Support\Str;

abstract class User implements Arrayable, ArrayAccess, Augmentable, Authenticatable, AuthorizableContract, CanResetPasswordContract, ContainsQueryableValues, HasLocalePreference, ResolvesValuesContract, SearchableContract, UserContract
{
    use Authorizable, CanResetPassword, ContainsComputedData, HasAugmentedInstance, HasAvatar, HasDirtyState, Notifiable, ResolvesValues, Searchable, TracksQueriedColumns, TracksQueriedRelations;

    protected $afterSaveCallbacks = [];
    protected $withEvents = true;

    abstract public function data($data = null);

    abstract public function get($key, $fallback = null);

    abstract public function has($key);

    abstract public function set($key, $value);

    abstract public function remove($key);

    public function value($key)
    {
        if ($this->hasComputedCallback($key)) {
            return $this->getComputed($key);
        }

        return $this->get($key);
    }

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
            $name = (string) $this->email();
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
        if (! $id = $this->id()) {
            return null;
        }

        return cp_route('users.edit', $id);
    }

    public function updateUrl()
    {
        if (! $id = $this->id()) {
            return null;
        }

        return cp_route('users.update', $id);
    }

    public function apiUrl()
    {
        if (! $id = $this->id()) {
            return null;
        }

        return Statamic::apiRoute('users.show', $this->id());
    }

    public function newAugmentedInstance(): Augmented
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

    public function getKey()
    {
        return $this->id();
    }

    public function getAuthPassword()
    {
        return $this->password();
    }

    public function getAuthPasswordName()
    {
        return 'password';
    }

    public function hasRole($role)
    {
        $role = $role instanceof RoleContract ? $role->handle() : $role;

        return $this->roles()->has($role);
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

    public function saveQuietly()
    {
        $this->withEvents = false;

        return $this->save();
    }

    public function save()
    {
        $isNew = is_null(Facades\User::find($this->id()));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];

        if ($withEvents) {
            if ($isNew && UserCreating::dispatch($this) === false) {
                return false;
            }

            if (UserSaving::dispatch($this) === false) {
                return false;
            }
        }

        Facades\User::save($this);

        foreach ($afterSaveCallbacks as $callback) {
            $callback($this);
        }

        if ($withEvents) {
            if ($isNew) {
                UserCreated::dispatch($this);
            }

            UserSaved::dispatch($this);
        }

        $this->syncOriginal();

        return $this;
    }

    public function deleteQuietly()
    {
        $this->withEvents = false;

        return $this->delete();
    }

    public function delete()
    {
        $withEvents = $this->withEvents;
        $this->withEvents = true;

        if ($withEvents && UserDeleting::dispatch($this) === false) {
            return false;
        }

        Facades\User::delete($this);

        if ($withEvents) {
            UserDeleted::dispatch($this);
        }

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

        if (is_array($broker)) {
            $broker = $broker['cp'];
        }

        return Password::broker($broker)->createToken($this);
    }

    public function generateActivateAccountToken()
    {
        $broker = config('statamic.users.passwords.'.PasswordReset::BROKER_ACTIVATIONS);

        if (is_array($broker)) {
            $broker = $broker['cp'];
        }

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

    public function shallowAugmentedArrayKeys()
    {
        return ['id', 'name', 'email', 'api_url'];
    }

    protected function defaultAugmentedRelations()
    {
        return $this->selectedQueryRelations;
    }

    public function preferredLocale()
    {
        return $this->getPreference('locale') ?? config('app.locale');
    }

    public function setPreferredLocale($locale)
    {
        return $this->setPreference('locale', $locale);
    }

    public function preferredTheme()
    {
        return $this->getPreference('theme') ?? 'auto';
    }

    public function getCpSearchResultBadge(): string
    {
        return __('User');
    }

    public function getElevatedSessionMethod(): string
    {
        return $this->password() ? 'password_confirmation' : 'verification_code';
    }

    protected function getComputedCallbacks()
    {
        return Facades\User::getComputedCallbacks();
    }

    public function getQueryableValue(string $field)
    {
        if (method_exists($this, $method = Str::camel($field))) {
            return $this->{$method}();
        }

        $value = $this->get($field);

        if (! $field = $this->blueprint()->field($field)) {
            return $value;
        }

        return $field->fieldtype()->toQueryableValue($value);
    }
}
