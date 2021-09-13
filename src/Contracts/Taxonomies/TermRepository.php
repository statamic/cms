<?php

namespace Statamic\Contracts\Taxonomies;

interface TermRepository
{
    public function all();

    public function whereTaxonomy(string $handle);

    public function whereInTaxonomy(array $handles);

    public function find($id);

    public function findByUri(string $uri);

    /** @deprecated */
    public function findBySlug(string $slug, string $collection);

    public function make(string $slug = null);

    public function query();

    public function save($entry);

    public function delete($entry);
}
