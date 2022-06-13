<?php

namespace Statamic\Structures;

use Statamic\Contracts\Structures\Nav as Contract;
use Statamic\Contracts\Structures\NavTreeRepository;
use Statamic\Data\ExistsAsFile;
use Statamic\Events\NavBlueprintFound;
use Statamic\Events\NavDeleted;
use Statamic\Events\NavSaved;
use Statamic\Facades;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;

class Nav extends Structure implements Contract
{
    use ExistsAsFile;

    protected $collections;

    public function save()
    {
        Facades\Nav::save($this);

        NavSaved::dispatch($this);

        return true;
    }

    public function delete()
    {
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
                });
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
        return new NavTree;
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

    public function existsIn($site)
    {
        return $this->trees()->has($site);
    }

    public function blueprint()
    {
        $blueprint = Blueprint::find('navigation.'.$this->handle())
            ?? Blueprint::makeFromFields([])->setHandle($this->handle())->setNamespace('navigation');

        NavBlueprintFound::dispatch($blueprint, $this);

        return $blueprint;
    }
}
