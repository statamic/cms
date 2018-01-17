<?php

namespace Statamic\CP\Navigation;

use Statamic\API\AssetContainer;
use Statamic\API\Collection;
use Statamic\API\Taxonomy;
use Statamic\API\User;
use Statamic\API\Folder;
use Statamic\API\GlobalSet;
use Statamic\Outpost as StatamicOutpost;

class NavFactory
{
    /**
     * @var Nav
     */
    private $nav;

    /**
     * @var StatamicOutpost
     */
    private $outpost;

    public function __construct(Nav $nav, StatamicOutpost $outpost)
    {
        $this->nav = $nav;
        $this->outpost = $outpost;
    }

    public function build()
    {
        $this->nav->add($this->buildContentNav());
        $this->nav->add($this->buildToolsNav());
        $this->nav->add($this->buildConfigureNav());
    }

    private function buildContentNav()
    {
        $nav = $this->item('content')->title(__('Content'));

        if ($this->access('pages:view')) {
            $nav->add($this->item('pages')->route('pages')->title(__('Pages')));
        }

        if ($this->access('collections:*:view')) {
            if ($sub = $this->buildCollectionsNav()) {
                $nav->add($sub);
            }
        }

        if ($this->access('taxonomies:*:view')) {
            if ($sub = $this->buildTaxonomiesNav()) {
                $nav->add($sub);
            }
        }

        if ($this->access('assets:*:view')) {
            if (! AssetContainer::all()->isEmpty()) {
                $nav->add($this->item('assets')->route('assets')->title(__('Assets')));
            }
        }

        if ($this->access('globals:*:view')) {
            $nav->add($this->buildGlobalsNav());
        }

        return $nav;
    }

    private function buildCollectionsNav()
    {
        $collections = collect(Collection::all())->filter(function ($collection) {
            return $this->access("collections:{$collection->path()}:view");
        });

        if ($collections->isEmpty()) {
            return;
        }

        if ($collections->count() === 1) {
            return $this->item('collections')->route('collections')->title($collections->first()->title());
        }

        $nav = $this->item('collections')->route('collections')->title(__('Collections'));

        foreach ($collections as $slug => $collection) {
            $nav->add(
                $this->item("collections:$slug")
                     ->route('entries.show', $slug)
                     ->title($collection->title())
            );
        }

        return $nav;
    }

    private function buildTaxonomiesNav()
    {
        $nav = $this->item('taxonomies')->route('taxonomies')->title(__('Taxonomies'));

        $taxonomies = collect(Taxonomy::all())->filter(function ($taxonomy) {
            return $this->access("taxonomies:{$taxonomy->path()}:view");
        });

        if ($taxonomies->isEmpty()) {
            return;
        }

        if ($taxonomies->count() === 1) {
            return $this->item('taxonomies')->route('taxonomies')->title($taxonomies->first()->title());
        }

        foreach ($taxonomies as $slug => $taxonomy) {
            $nav->add(
                $this->item("taxonomies:$slug")
                    ->route('terms.show', $slug)
                    ->title($taxonomy->title())
            );
        }

        return $nav;
    }

    private function buildGlobalsNav()
    {
        $nav = $this->item('globals')->route('globals')->title(__('Globals'));

        $globals = GlobalSet::all()->filter(function ($set) {
            return $this->access("globals:{$set->slug()}:view");
        });

        if (count($globals) > 1) {
            foreach ($globals as $set) {
                $nav->add(
                    $this->item("globals:{$set->slug()}")
                         ->url($set->editUrl())
                         ->title($set->title())
                );
            }
        }

        return $nav;
    }

    private function buildToolsNav()
    {
        $nav = $this->item('tools')->title(__('Tools'));

        if ($this->access('forms')) {
            $nav->add($this->item('forms')->route('forms')->title(_('Forms')));
        }

        if ($this->access('updater')) {
            $updates = $this->outpost->getUpdateCount();

            $nav->add(
                $this->item('updater')
                     ->route('updater')
                     ->title(__('Updater'))
                     ->badge($updates)
            );
        }

        $duplicates = app('stache')->duplicates();
        if (! $duplicates->isEmpty()) {
            $nav->add(
                $this->item('resolve_duplicates')
                     ->route('resolve-duplicate-ids')
                     ->title(__('Duplicate IDs'))
                     ->badge($duplicates->count())
            );
        }

        return $nav;
    }

    private function buildConfigureNav()
    {
        $nav = $this->item('configure')->title(__('Configure'));

        if ($this->access('super')) {
            $nav->add($this->item('addons')->route('addons')->title(__('Addons')));
            $nav->add($this->buildConfigureContentNav());
            $nav->add($this->item('fieldsets')->route('fieldsets')->title(__('Fieldsets')));
        }

        if ($this->access('users:view')) {
            $nav->add($this->buildUsersNav());
        }

        return $nav;
    }

    private function buildConfigureContentNav()
    {
        $nav = $this->item('config-content')->route('content')->title(__('Content'));

        $nav->add($this->item('assets')->route('assets.containers.manage')->title(__('Assets')));
        $nav->add($this->item('collections')->route('collections.manage')->title(__('Collections')));
        $nav->add($this->item('taxonomies')->route('taxonomies.manage')->title(__('Taxonomies')));
        $nav->add($this->item('globals')->route('globals.manage')->title(__('Globals')));

        return $nav;
    }

    private function buildUsersNav()
    {
        $nav = $this->item('users')->route('users')->title(__('Users'));

        if ($this->access('super')) {
            $nav->add($this->item('user-groups')->route('user.groups')->title(__('User Groups')));
            $nav->add($this->item('user-roles')->route('user.roles')->title(__('User Roles')));
        }

        return $nav;
    }

    private function item($name)
    {
        $item = new NavItem;

        $item->name($name);

        return $item;
    }

    private function access($key)
    {
        if (! User::loggedIn()) {
            return false;
        }

        return User::getCurrent()->can($key);
    }
}
