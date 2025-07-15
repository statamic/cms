<?php

namespace Statamic\Contracts\Entries;

interface EntryRepository
{
    public function all();

    public function whereCollection(string $handle);

    public function whereInCollection(array $handles);

    public function find($id);

    public function findOrFail($id);

    public function findByUri(string $uri);

    public function findByIds($ids);

    public function make();

    public function query();

    public function save($entry);

    public function delete($entry);

    public function createRules($collection, $site);

    public function updateRules($collection, $entry);
}
