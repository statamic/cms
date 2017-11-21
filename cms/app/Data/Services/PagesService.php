<?php

namespace Statamic\Data\Services;

use Statamic\Data\Pages\PageCollection;

class PagesService extends BaseService
{
    /**
     * The repo key
     *
     * @var string
     */
    protected $repo = 'pages';

    /**
     * {@inheritdoc}
     * @return PageCollection
     */
    public function all()
    {
        return collect_pages(parent::all());
    }

    /**
     * Get the URIs for a specific locale
     *
     * @param string $locale
     * @return array
     */
    public function localizedUris($locale)
    {
        return $this->repo()->getUris($locale);
    }
}