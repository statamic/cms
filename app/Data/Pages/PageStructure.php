<?php

namespace Statamic\Data\Pages;

use Statamic\API\URL;
use Statamic\API\Path;

class PageStructure
{
    /**
     * ID of the page
     *
     * @var string
     */
    private $id;

    /**
     * @var array
     */
    private $data;

    /**
     * @param string $id  ID of the underlying page
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Get the underlying page
     *
     * @return \Statamic\Contracts\Data\Pages\Page
     */
    public function page()
    {
        return \Statamic\API\Page::find($this->id);
    }

    /**
     * Get the underlying page ID
     *
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * Get the underlying page path
     *
     * @return string
     */
    public function path()
    {
        return $this->page()->path();
    }

    /**
     * Set the array representation
     *
     * @param array $data
     */
    public function data(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the page structure in an array representation
     *
     * @return array
     */
    public function toArray()
    {
        if ($this->data) {
            return $this->data;
        }

        $path = $this->page()->path();
        $uri = $this->page()->uri();

        return [
            'url'    => $this->page()->url(),
            'uri'    => $uri,
            'parent' => ($uri == '/') ? null : URL::parent($uri),
            'depth'  => ($uri == '/') ? 0 : substr_count($uri, '/'),
            'status' => Path::status($path),
        ];
    }
}
