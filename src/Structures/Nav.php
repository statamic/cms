<?php

namespace Statamic\Structures;

use Statamic\Contracts\Structures\Nav as Contract;
use Statamic\Contracts\Structures\NavTree;
use Statamic\Contracts\Structures\NavTreeRepository;
use Statamic\Data\ExistsAsFile;
use Statamic\Events\NavBlueprintFound;
use Statamic\Events\NavCreated;
use Statamic\Events\NavCreating;
use Statamic\Events\NavDeleted;
use Statamic\Events\NavDeleting;
use Statamic\Events\NavSaved;
use Statamic\Events\NavSaving;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;

class Nav extends Structure implements Contract
{
    use ExistsAsFile;

    protected $collections;
    protected $canSelectAcrossSites = false;
    private $blueprintCache;

    public function save()
    {
        $isNew = ! Facades\Nav::find($this->handle());

        if ($isNew && NavCreating::dispatch($this) === false) {
            return false;
        }

        if (NavSaving::dispatch($this) === false) {
            return false;
        }

        Facades\Nav::save($this);

        if ($isNew) {
            NavCreated::dispatch($this);
        }

        NavSaved::dispatch($this);

        return true;
    }

    public function delete()
    {
        if (NavDeleting::dispatch($this) === false) {
            return false;
        }

        Facades\Nav::delete($this);

        NavDeleted::dispatch($this);

        return true;
    }

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('navigation')->directory(), '/'),
            $this->handle,
        ]);
    }

    public function fileData()
    {
        return [
            'title' => $this->title,
            'collections' => $this->collections,
            'select_across_sites' => $this->canSelectAcrossSites ? true : null,
            'max_depth' => $this->maxDepth,
            'root' => $this->expectsRoot ?: null,
        ];
    }

    public function collections($collections = null)
    {
        return $this
            ->fluentlyGetOrSet('collections')
            ->getter(function ($collections) {
                return collect($collections)->map(function ($collection) {
                    return Collection::findByHandle($collection);
                })->filter();
            })
            ->args(func_get_args());
    }

    public function showUrl($params = [])
    {
        return cp_route('navigation.show', array_merge($params, [
            'navigation' => $this->handle(),
        ]));
    }

    public function editUrl()
    {
        return cp_route('navigation.edit', $this->handle());
    }

    public function deleteUrl()
    {
        return cp_route('navigation.destroy', $this->handle());
    }

    public function newTreeInstance()
    {
        return app(NavTree::class);
    }

    public function in($site)
    {
        return app(NavTreeRepository::class)->find($this->handle(), $site);
    }

    public function trees()
    {
        return Site::all()->map(function ($site) {
            return $this->in($site);
        })->filter();
    }

    public function sites()
    {
        return $this->trees()->keys();
    }

    public function existsIn($site)
    {
        return $this->trees()->has($site);
    }

    public function blueprint()
    {
        if ($this->blueprintCache) {
            return $this->blueprintCache;
        }

        if (Blink::has($blink = 'nav-blueprint-'.$this->handle())) {
            return $this->blueprintCache = Blink::get($blink);
        }

        $blueprint = Blueprint::find('navigation.'.$this->handle())
            ?? Blueprint::makeFromFields([])->setHandle($this->handle())->setNamespace('navigation');

        Blink::put($blink, $this->blueprintCache = $blueprint);

        NavBlueprintFound::dispatch($blueprint, $this);

        return $blueprint;
    }

    public function canSelectAcrossSites($canSelect = null)
    {
        return $this
            ->fluentlyGetOrSet('canSelectAcrossSites')
            ->args(func_get_args());
    }
}
