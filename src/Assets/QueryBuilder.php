<?php

namespace Statamic\Assets;

use Exception;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Contracts\Assets\QueryBuilder as Contract;
use Statamic\Facades;
use Statamic\Query\IteratorBuilder as BaseQueryBuilder;

class QueryBuilder extends BaseQueryBuilder implements Contract
{
    protected $container;
    protected $folder;
    protected $recursive = false;

    protected function getBaseItems()
    {
        $container = $this->container instanceof AssetContainer
            ? $this->container
            : Facades\AssetContainer::find($this->container);

        $recursive = $this->folder ? $this->recursive : true;

        return $this->collect($container->files($this->folder, $recursive)->map(function ($path) use ($container) {
            return $container->asset($path);
        }));
    }

    public function where($column, $operator = null, $value = null)
    {
        if ($column === 'container') {
            throw_if($this->container, new Exception('Only one asset container may be queried.'));
            $this->container = $operator;

            return $this;
        }

        if ($column === 'folder') {
            throw_if($this->folder, new Exception('Only one folder may be queried.'));

            if ($operator === 'like') {
                throw_if(starts_with($value, '%'), new Exception('Cannot perform LIKE query on folder with starting wildcard.'));
                $this->folder = str_before($value, '%');
                $this->recursive = true;
            } else {
                $this->folder = $operator;
                $this->recursive = false;
            }

            return $this;
        }

        return parent::where($column, $operator, $value);
    }

    protected function collect($items = [])
    {
        return AssetCollection::make($items);
    }
}
