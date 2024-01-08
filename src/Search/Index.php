<?php

namespace Statamic\Search;

use Statamic\Contracts\Search\Searchable;
use Statamic\Support\Arr;

abstract class Index
{
    protected $name;
    protected $locale;
    protected $config;

    abstract public function search($query);

    abstract public function delete($document);

    abstract public function exists();

    abstract protected function insertDocuments(Documents $documents);

    abstract protected function deleteIndex();

    public function __construct($name, array $config, string $locale = null)
    {
        $this->name = $locale ? $name.'_'.$locale : $name;
        $this->config = $config;
        $this->locale = $locale;
    }

    public function name()
    {
        return $this->name;
    }

    public function title()
    {
        return $this->config['title'] ?? title_case($this->name);
    }

    public function config()
    {
        return $this->config;
    }

    public function locale()
    {
        return $this->locale;
    }

    public function for($query)
    {
        return $this->search($query);
    }

    public function update()
    {
        $this->deleteIndex();

        $this->insertMultiple($this->searchables()->all());

        return $this;
    }

    public function ensureExists()
    {
        if (! $this->exists()) {
            $this->update();
        }

        return $this;
    }

    public function insert($document)
    {
        return $this->insertMultiple(Arr::wrap($document));
    }

    public function insertMultiple($documents)
    {
        $documents = (new Documents($documents))->mapWithKeys(function (Searchable $item) {
            return [$item->getSearchReference() => $this->searchables()->fields($item)];
        });

        $this->insertDocuments($documents);

        return $this;
    }

    public function shouldIndex($searchable)
    {
        return $this->searchables()->contains($searchable);
    }

    public function searchables()
    {
        return new Searchables($this);
    }

    public function extraAugmentedResultData(Result $result)
    {
        return [];
    }
}
