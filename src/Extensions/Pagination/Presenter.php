<?php

namespace Statamic\Extensions\Pagination;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\UrlWindow;

class Presenter
{
    protected $paginator;
    protected $window;

    public function __construct(Paginator $paginator)
    {
        $this->paginator = $paginator;
        $this->window = UrlWindow::make($paginator);
    }

    public function render()
    {
        if (! $this->paginator->hasPages()) {
            return [];
        }

        return [
            'all' => $this->getAllPages(),
            'segments' => $this->getWindow(),
        ];
    }

    /**
     * Simply iterate over all the pages.
     *
     * @return array
     */
    private function getAllPages()
    {
        $pages = [];
        $i = 1;

        while ($i <= $this->paginator->lastPage()) {
            $pages[] = [
                'page' => $i,
                'url'  => $this->paginator->url($i),
            ];

            $i++;
        }

        return $pages;
    }

    /**
     * Get the windowed arrays of links.
     *
     * @return array
     */
    private function getWindow()
    {
        $window = $this->window;

        $window['first'] = (is_array($this->window['first']))
            ? $this->associative($this->window['first'])
            : [];

        $window['slider'] = (is_array($this->window['slider']))
            ? $this->associative($this->window['slider'])
            : [];

        $window['last'] = (is_array($this->window['last']))
            ? $this->associative($this->window['last'])
            : [];

        return $window;
    }

    /**
     * Convert an array to an associative version.
     *
     * @param  array  $array
     * @return array
     */
    private function associative($array)
    {
        $mapped = [];

        foreach ($array as $key => $value) {
            $mapped[] = [
                'page' => $key,
                'url'  => $value,
            ];
        }

        return $mapped;
    }
}
