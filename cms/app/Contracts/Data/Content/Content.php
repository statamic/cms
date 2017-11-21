<?php

namespace Statamic\Contracts\Data\Content;

use Statamic\Contracts\Data\Data;

interface Content extends Data
{
    /**
     * Get or set the slug
     *
     * @param string|null $slug
     * @return mixed
     */
    public function slug($slug = null);

    /**
     * Get or set the order key
     *
     * @param mixed|null $order
     * @return mixed
     */
    public function order($order = null);

    /**
     * Get or set the publish status
     *
     * @param null|bool $published
     * @return void|bool
     */
    public function published($published = null);

    /**
     * Publish the content
     *
     * @return void
     */
    public function publish();

    /**
     * Unpublishes the content
     *
     * @return void
     */
    public function unpublish();

    /**
     * Get or set the URI
     *
     * This is the "identifying URL" for lack of a better description.
     * For instance, where `/fr/blog/my-post` would be a URL, `/blog/my-post` would be the URI.
     *
     * @param string|null $uri
     * @return mixed
     */
    public function uri($uri = null);

    /**
     * Get the URL
     *
     * @return string
     */
    public function url();

    /**
     * Get the full, absolute URL
     *
     * @return string
     */
    public function absoluteUrl();

    /**
     * Get the folder of the file relative to content path
     *
     * @return string
     */
    public function folder();

    /**
     * Get the content type
     *
     * @return string
     */
    public function contentType();

    /**
     * Cause taxonomies to be added when supplementing occurs
     *
     * @return void
     */
    public function supplementTaxonomies();
}
