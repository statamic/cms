<?php

namespace Statamic\Data\Taxonomies;

use Statamic\API\Stache;
use Statamic\API\Blueprint;
use Statamic\FluentlyGetsAndSets;
use Statamic\Contracts\Data\Taxonomies\Taxonomy as Contract;

class Taxonomy implements Contract
{
    use FluentlyGetsAndSets;

    protected $handle;
    protected $title;

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function title($title = null)
    {
        return $this
            ->fluentlyGetOrSet('title')
            ->getter(function ($title) {
                return $title ?? ucfirst($this->handle);
            })
            ->args(func_get_args());
    }

    public function editUrl()
    {
        return cp_route('taxonomies.edit', $this->handle());
    }

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('taxonomies')->directory(), '/'),
            $this->handle
        ]);
    }

    public function termBlueprint()
    {
        return Blueprint::find('default'); // todo
    }

    public function sortField()
    {
        return 'title'; // todo
    }

    public function sortDirection()
    {
        return 'asc'; // todo
    }
}
