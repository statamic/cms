<?php

namespace Statamic\Eloquent\Entries;

use Statamic\API;
use Statamic\Data\Entries\EntryCollection;
use Statamic\Contracts\Data\Entries\Entry as EntryContract;
use Statamic\Contracts\Data\Repositories\EntryRepository as RepositoryContract;

class Repository implements RepositoryContract
{
    public function query()
    {
        return new QueryBuilder(Model::query());
    }

    public function find($id): ?EntryContract
    {
        if (! $model = Model::find($id)) {
            return null;
        }

        return Entry::fromModel($model);
    }

    public function all(): EntryCollection
    {
        return $this->transform(Model::all());
    }

    public function whereCollection(string $handle): EntryCollection
    {
        return $this->transform(
            Model::where('collection', $handle)->get()
        );
    }

    public function whereInCollection(array $handles): EntryCollection
    {

    }

    public function findBySlug(string $slug, string $collection): ?EntryContract
    {

    }

    public function findByUri(string $uri): ?EntryContract
    {

    }

    public function save($entry)
    {
        $model = $entry->toModel();

        $model->save();

        $entry->model($model);

    }

    public function make()
    {
        return new Entry;
    }

    protected function transform($models)
    {
        // Convert the eloquent model to one of the Statamic Entry objects.
        return collect_entries($models->map(function ($model) {
            return Entry::fromModel($model);
        }));
    }

    public function eloquentModelToStatamicEntry($model)
    {
        return Entry::fromModel($model);
    }

    public function delete($entry)
    {
        $entry->model()->delete();
    }
}
