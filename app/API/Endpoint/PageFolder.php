<?php

namespace Statamic\API\Endpoint;

use Statamic\Data\Services\PageFoldersService;

class PageFolder
{
    /**
     * Get all page folders
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return app(PageFoldersService::class)->all();
    }

    /**
     * Get a page folder by handle
     *
     * @param string $handle
     * @return \Statamic\Contracts\Data\Pages\PageFolder
     */
    public function whereHandle($handle)
    {
        return app(PageFoldersService::class)->handle($handle);
    }

    /**
     * Check if a page folder exists by its handle
     *
     * @param string $handle
     * @return bool
     */
    public function handleExists($handle)
    {
        return self::whereHandle($handle) !== null;
    }

    /**
     * Create a page folder
     *
     * @return \Statamic\Contracts\Data\Pages\PageFolder
     */
    public function create()
    {
        /** @var \Statamic\Contracts\Data\Pages\PageFolder $folder */
        $folder = app('Statamic\Contracts\Data\Pages\PageFolder');

        return $folder;
    }
}