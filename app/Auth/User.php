<?php

namespace Statamic\Auth;

use Statamic\API;
use Statamic\API\Blueprint;
use Statamic\Data\Augmentable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Statamic\Contracts\Auth\User as UserContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Statamic\Contracts\Data\Augmentable as AugmentableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

abstract class User implements UserContract, Authenticatable, CanResetPasswordContract, AugmentableContract, Arrayable
{
    use Authorizable, Notifiable, CanResetPassword, Augmentable;

    abstract public function get($key, $fallback = null);
    abstract public function has($key);
    abstract public function set($key, $value);

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

    protected function augmentedArrayData()
    {
        return $this->data();
    }

    public function avatar($size = 64)
    {
        if ($this->has('avatar')) {
            return $this->augment('avatar')->value()->url();
        }

        return config('statamic.users.avatars') === 'gravatar'
            ? gravatar($this->email(), $size)
            : null;
    }

    public function isTaxonomizable()
    {
        return true;
    }

    public function editUrl()
    {
        return cp_route('users.edit', $this->id());
    }

    /**
     * Add supplemental data to the attributes
     */
    public function supplement()
    {
        $this->supplements['last_modified'] = $this->lastModified()->timestamp;
        $this->supplements['email'] = $this->email();
        $this->supplements['edit_url'] = $this->editUrl();

        if ($first_name = $this->get('first_name')) {
            $name = $first_name;

            if ($last_name = $this->get('last_name')) {
                $name .= ' ' . $last_name;
            }

            $this->supplements['name'] = $name;
        }

        foreach ($this->roles() as $role) {
            $this->supplements['is_'.$role->handle()] = true;
        }

        foreach ($this->groups() as $group) {
            $this->supplements['in_'.$group->handle()] = true;
        }

        if ($this->supplement_taxonomies) {
            $this->addTaxonomySupplements();
        }
    }

    public function toArray()
    {
        $roles = $this->roles()->mapWithKeys(function ($role) {
            return ["is_{$role->handle()}" => true];
        })->all();

        $groups = $this->groups()->mapWithKeys(function ($group) {
            return ["in_{$group->handle()}" => true];
        })->all();

        return array_merge($this->data(), [
            'id' => $this->id(),
            'title' => $this->title(),
            'email' => $this->email(),
            'avatar' => $this->avatar(),
            'initials' => $this->initials(),
            'preferences' => $this->preferences(),
            'edit_url' => $this->editUrl(),
        ], $roles, $groups, $this->supplements);
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
        API\User::save($this);

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
}
