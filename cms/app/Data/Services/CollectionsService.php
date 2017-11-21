<?php

namespace Statamic\Data\Services;

class CollectionsService extends BaseService
{
    /**
     * The repo key
     *
     * @var string
     */
    protected $repo = 'collections';

    /**
     * {@inheritdoc}
     */
    public function handle($handle)
    {
        return $this->repo()->getItem($handle);
    }
}