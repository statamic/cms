<?php

namespace Statamic\Data\Pages;

use Statamic\Data\Content\ContentFactory;
use Statamic\Contracts\Data\Pages\PageFactory as PageFactoryContract;

class PageFactory extends ContentFactory implements PageFactoryContract
{
    private $uri;

    /**
     * @param string $uri
     * @return $this
     */
    public function create($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return Page
     */
    public function get()
    {
        $page = new Page;

        $page->uri($this->uri);
        $page->data($this->data);
        $page->order($this->order);
        $page->published($this->published);

        if ($this->path) {
            $page->path($this->path);
            $page->dataType(pathinfo($this->path)['extension']);
        }

        $page = $this->identify($page);

        $page->syncOriginal();

        return $page;
    }
}
