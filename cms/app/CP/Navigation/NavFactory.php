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
        $nav = $this->item('content')->title(t('nav_content'));

        if ($this->access('pages:edit')) {
            $nav->add($this->item('pages')->route('pages')->title(t('nav_pages')));
        }

        if ($this->access('collections:*:edit')) {
            if ($sub = $this->buildCollectionsNav()) {
                $nav->add($sub);
            }
        }

        if ($this->access('taxonomies:*:edit')) {
            if ($sub = $this->buildTaxonomiesNav()) {
                $nav->add($sub);
            }
        }

        if ($this->access('assets:*:edit')) {
            if (! AssetContainer::all()->isEmpty()) {
                $nav->add($this->item('assets')->route('assets')->title(t('nav_assets')));
            }
        }

        if ($this->access('globals:*:edit')) {
            $nav->add($this->buildGlobalsNav());
        }

        return $nav;
    }

    private function buildCollectionsNav()
    {
        $collections = collect(Collection::all())->filter(function ($collection) {
            return $this->access("collections:{$collection->path()}:edit");
        });

        if ($collections->isEmpty()) {
            return;
        }

        if ($collections->count() === 1) {
            return $this->item('collections')->route('collections')->title($collections->first()->title());
        }

        $nav = $this->item('collections')->route('collections')->title(t('nav_collections'));

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
        $nav = $this->item('taxonomies')->route('taxonomies')->title(t('nav_taxonomies'));

        $taxonomies = collect(Taxonomy::all())->filter(function ($taxonomy) {
            return $this->access("taxonomies:{$taxonomy->path()}:edit");
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
        $nav = $this->item('globals')->route('globals')->title(t('nav_globals'));

        $globals = GlobalSet::all()->filter(function ($set) {
            return $this->access("globals:{$set->slug()}:edit");
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
        $nav = $this->item('tools')->title(t('nav_tools'));

        if ($this->access('forms')) {
            $nav->add($this->item('forms')->route('forms')->title(t('nav_forms')));
        }

        if ($this->access('updater')) {
            $updates = $this->outpost->getUpdateCount();

            $nav->add(
                $this->item('updater')
                     ->route('updater')
                     ->title(t('nav_updater'))
                     ->badge($updates)
            );
        }

        if ($this->access('importer')) {
            $nav->add($this->item('import')->route('import')->title(t('nav_import')));
        }

        $duplicates = app('stache')->duplicates();
        if (! $duplicates->isEmpty()) {
            $nav->add(
                $this->item('resolve_duplicates')
                     ->route('resolve-duplicate-ids')
                     ->title(t('nav_resolve_duplicate_ids'))
                     ->badge($duplicates->count())
            );
        }

        return $nav;
    }

    private function buildConfigureNav()
    {
        $nav = $this->item('configure')->title(t('nav_configure'));

        if ($this->access('super')) {
            $nav->add($this->item('addons')->route('addons')->title(t('nav_addons')));
            $nav->add($this->buildConfigureContentNav());
            $nav->add($this->item('fieldsets')->route('fieldsets')->title(t('nav_fieldsets')));
            $nav->add($this->buildConfigureSettingsNav());
        }

        if ($this->access('users:edit')) {
            $nav->add($this->buildUsersNav());
        }

        return $nav;
    }

    private function buildConfigureContentNav()
    {
        $nav = $this->item('config-content')->route('content')->title(t('nav_content'));

        $nav->add($this->item('assets')->route('assets.containers.manage')->title(t('nav_assets')));
        $nav->add($this->item('collections')->route('collections.manage')->title(t('nav_collections')));
        $nav->add($this->item('taxonomies')->route('taxonomies.manage')->title(t('nav_taxonomies')));
        $nav->add($this->item('globals')->route('globals.manage')->title(t('nav_globals')));

        return $nav;
    }

    private function buildConfigureSettingsNav()
    {
        $nav = $this->item('settings')->route('settings')->title(t('nav_settings'));

        $sections = collect(Folder::getFilesByType(statamic_path('settings/defaults'), 'yaml'))
            ->map(function ($file) {
                return pathinfo($file)['filename'];
            })
            ->reject(function ($setting) {
                return $setting == 'services';
            });

        foreach ($sections as $section) {
            $nav->add(
                $this
                    ->item($section)
                    ->route('settings.edit', $section)
                    ->title(t("settings_$section"))
            );
        }

        return $nav;
    }

    private function buildUsersNav()
    {
        $nav = $this->item('users')->route('users')->title(t('nav_users'));

        if ($this->access('super')) {
            $nav->add($this->item('user-groups')->route('user.groups')->title(t('nav_user-groups')));
            $nav->add($this->item('user-roles')->route('user.roles')->title(t('nav_user-roles')));
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
