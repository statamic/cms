<?php

namespace Statamic\Data\Structures;

use Statamic\API\Site;
use Statamic\API\Entry;
use Statamic\API\Stache;
use Statamic\Data\Localizable;
use Statamic\Data\ExistsAsFile;
use Statamic\FluentlyGetsAndSets;
use Statamic\API\Structure as StructureAPI;
use Statamic\Contracts\Data\Structures\Structure as StructureContract;

class Structure implements StructureContract
{
    use Localizable, FluentlyGetsAndSets, ExistsAsFile;

    protected $title;
    protected $handle;
    protected $sites;

    public function id()
    {
        return $this->handle();
    }

    public function handle($handle = null)
    {
        if (is_null($handle)) {
            return $this->handle;
        }

        $this->handle = $handle;

        return $this;
    }

    public function title($title = null)
    {
        return $this->fluentlyGetOrSet('title')->args(func_get_args());
    }

    public function sites($sites = null)
    {
        return $this
            ->fluentlyGetOrSet('sites')
            ->getter(function ($sites) {
                return collect(Site::hasMultiple() ? $sites : [Site::default()->handle()]);
            })
            ->args(func_get_args());
    }

    public function showUrl()
    {
        return cp_route('structures.show', $this->handle());
    }

    public function editUrl()
    {
        return cp_route('structures.edit', $this->handle());
    }

    public function save()
    {
        StructureAPI::save($this);
    }

    public function toCacheableArray()
    {
        return $this->data;
    }

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('structures')->directory(), '/'),
            $this->handle
        ]);
    }

    public function makeTree()
    {
        return (new Tree)->structure($this);
    }

    public function makeLocalization()
    {
        return $this->makeTree();
    }
}
