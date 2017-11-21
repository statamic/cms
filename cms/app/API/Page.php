<?php

namespace Statamic\API;

use Statamic\Data\Services\PagesService;

class Page
{
    /**
     * The service for interacting with pages
     *
     * @return PagesService
     */
    private static function service()
    {
        return app(PagesService::class);
    }

    /**
     * Find a page by ID
     *
     * @param string $id
     * @return \Statamic\Contracts\Data\Pages\Page
     */
    public static function find($id)
    {
        return self::service()->id($id);
    }

    /**
     * Get all pages
     *
     * @return \Statamic\Data\Pages\PageCollection
     */
    public static function all()
    {
        return self::service()->all();
    }

    /**
     * Find a page by URI
     *
     * @param string $uri
     * @return \Statamic\Contracts\Data\Pages\Page
     */
    public static function whereUri($uri)
    {
        return self::service()->uri($uri);
    }

    /**
     * Check if a page exists
     *
     * @param string $id
     * @return bool
     */
    public static function exists($id)
    {
        return self::service()->exists($id);
    }

    /**
     * Check if a page exists by URI
     *
     * @param string $uri
     * @return bool
     */
    public static function uriExists($uri)
    {
        return self::service()->uriExists($uri);
    }

    /**
     * Create a page
     *
     * @param string $uri
     * @return \Statamic\Contracts\Data\Pages\PageFactory
     */
    public static function create($uri)
    {
        return app('Statamic\Contracts\Data\Pages\PageFactory')->create($uri);
    }

    /**
     * Get a page by UUID
     *
     * @param string $uuid
     * @return \Statamic\Contracts\Data\Pages\Page
     * @deprecated since 2.1
     */
    public static function getByUuid($uuid)
    {
        \Log::notice('Page::getByUuid() is deprecated. Use Page::find()');

        return self::find($uuid);
    }

    /**
     * Get a page by its URL
     *
     * @param string      $url
     * @return \Statamic\Contracts\Data\Pages\Page
     * @deprecated since 2.1
     */
    public static function getByUrl($url)
    {
        \Log::notice('Page::getByUrl() is deprecated. Use Page::whereUri()');

        return self::whereUri($url);
    }
}
