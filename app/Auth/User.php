<?php

namespace Statamic\Auth;

use Statamic\API;
use Statamic\API\Blueprint;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Statamic\Contracts\Auth\User as UserContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

abstract class User implements UserContract, Authenticatable, CanResetPasswordContract
{
    use Authorizable, Notifiable, CanResetPassword;

    public function initials()
    {
        if ($name = $this->get('name')) {
            list($first, $last) = explode(' ', $name);
        } else {
            $first = $this->email();
            $last = '';
        }

        return strtoupper(substr($first, 0, 1) . substr($last, 0, 1));
    }

    public function avatar($size = 64)
    {
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
            'email' => $this->email(),
        ], $roles, $groups);
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
