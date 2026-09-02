<?php

namespace Statamic\Search;

use Closure;
use Statamic\Contracts\Search\Searchable;
use Statamic\Support\Str;

abstract class Index
{
    protected $name;
    protected $handle;
    protected $locale;
    protected $config;
    protected bool $shouldQueue = true;
    protected static ?Closure $nameCallback = null;

    abstract public function search($query);

    abstract public function delete($document);

    abstract public function exists();

    abstract public function insertDocuments(Documents $documents);

    abstract protected function deleteIndex();

    public function __construct($name, array $config, ?string $locale = null)
    {
        $this->handle = $name;

        $this->name = static::$nameCallback
            ? call_user_func(static::$nameCallback, $name, $locale)
            : ($locale ? $name.'_'.$locale : $name);

        $this->config = $config;
        $this->locale = $locale;
    }

    public function name()
    {
        return $this->name;
    }

    public function handle()
    {
        return $this->handle;
    }

    public static function resolveNameUsing(?Closure $callback)
    {
        static::$nameCallback = $callback;
    }

    public function title()
    {
        return $this->config['title'] ?? Str::title($this->name);
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

        $this->searchables()->lazy()->each(fn ($searchables) => $this->insertMultiple($searchables));

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
        return $this->insertMultiple(collect($document));
    }

    public function insertMultiple($documents)
    {
        $documents
            ->chunk(config('statamic.search.chunk_size'))
            ->each(function ($documents) {
                $job = new InsertMultipleJob(
                    name: $this->handle,
                    locale: $this->locale,
                    documents: $documents
                );

                $this->shouldQueue ? dispatch($job) : dispatch_sync($job);
            });

        return $this;
    }

    public function withoutQueue()
    {
        $this->shouldQueue = false;

        return $this;
    }

    public function fields(Searchable $searchable)
    {
        return $this->searchables()->fields($searchable);
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
