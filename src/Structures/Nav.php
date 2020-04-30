<?php

namespace Statamic\Structures;

use Statamic\Contracts\Structures\Nav as Contract;
use Statamic\Data\ExistsAsFile;
use Statamic\Facades;
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

        return true;
    }

    public function delete()
    {
        Facades\Nav::delete($this);

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
        $data = [
            'title' => $this->title,
            'collections' => $this->collections,
            'max_depth' => $this->maxDepth,
            'root' => $this->expectsRoot ?: null,
        ];

        if (! Site::hasMultiple()) {
            $data = array_merge($data, $this->in(Site::default()->handle())->fileData());
        }

        return $data;
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
}
