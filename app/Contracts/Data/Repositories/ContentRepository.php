<?php

namespace Statamic\Contracts\Data\Repositories;

use Statamic\Contracts\Data\Content\Content;
use Statamic\Data\Content\ContentCollection;

interface ContentRepository
{
    public function all(): ContentCollection;
    public function find($id): ?Content;
    public function findByUri(string $uri, string $locale = null): ?Content;
}
