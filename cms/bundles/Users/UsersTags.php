<?php

namespace Statamic\Addons\Users;

use Statamic\API\Role;
use Statamic\API\Term;
use Statamic\API\User;
use Statamic\API\UserGroup;
use Statamic\Addons\Collection\CollectionTags;

class UsersTags extends CollectionTags
{
    public function index()
    {
        $this->collection = $this->getCollection();

        if ($group = $this->get('group')) {
            $this->filterByGroup($group);
        }

        if ($role = $this->get('role')) {
            $this->filterByRole($role);
        }

        $this->filter();

        if ($this->collection->isEmpty()) {
            return $this->parseNoResults();
        }

        return $this->output();
    }

    private function getCollection()
    {
        if ($this->getBool('taxonomy')) {
            return $this->getTaxonomyCollection();
        }

        return collect_content(User::all());
    }

    private function getTaxonomyCollection()
    {
        $data = Term::whereSlug(
            array_get($this->context, 'page.default_slug'),
            array_get($this->context, 'page.taxonomy')
        );

        if (! $data) {
            return collect_content();
        }

        return $data->collection()->filter(function ($item) {
            return $item instanceof \Statamic\Contracts\Data\Users\User;
        });
    }

    public function getSortOrder()
    {
        return $this->get('sort', 'username');
    }

    protected function filterByGroup($group)
    {
        $group = UserGroup::whereHandle($group);

        $this->collection = $this->collection->filter(function ($user) use ($group) {
            return $user->inGroup($group);
        });
    }

    protected function filterByRole($role)
    {
        $role = Role::whereHandle($role);

        $this->collection = $this->collection->filter(function ($user) use ($role) {
            return $user->hasRole($role);
        });
    }
}
