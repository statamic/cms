<?php

namespace Statamic\Data\Services;

class PageFoldersService extends BaseService
{
    /**
     * The repo key
     *
     * @var string
     */
    protected $repo = 'pagefolders';

    /**
     * {@inheritdoc}
     */
    public function handle($handle)
    {
        return $this->repo()->getItem($handle);
    }
}