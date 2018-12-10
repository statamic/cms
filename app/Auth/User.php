<?php

namespace Statamic\Auth;

use Statamic\Data\Data;
use Statamic\API\Blueprint;
use Illuminate\Contracts\Auth\Authenticatable;
use Statamic\Contracts\Auth\User as UserContract;
use Illuminate\Foundation\Auth\Access\Authorizable;

abstract class User extends Data implements UserContract, Authenticatable
{
    use Authorizable;

    public function username($username = null)
    {
        return $this->email($username);
    }

    public function initials()
    {
        // TODO: Attempt to get initials from the name before using email.
        return strtoupper(substr($this->email(), 0, 1));
    }

    public function avatar($size = 64)
    {
        return config('statamic.users.gravatar') ? gravatar($this->email(), $size) : null;
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

        // TODO
        // foreach ($this->roles() as $role) {
        //     $this->supplements['is_'.Str::slug($role->title(), '_')] = true;
        // }

        // TODO
        // foreach ($this->groups() as $group) {
        //     $this->supplements['in_'.Str::slug($group->title(), '_')] = true;
        // }

        if ($this->supplement_taxonomies) {
            $this->addTaxonomySupplements();
        }
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
}