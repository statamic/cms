<?php

namespace Statamic\CP\Publish;

use Statamic\API\Helper;
use Statamic\API\URL;
use Statamic\API\Page;
use Statamic\API\Content;

class PagePublisher extends Publisher
{
    /**
     * The URI of the current page's parent.
     *
     * @var string
     */
    protected $parent_uri;

    /**
     * Perform initial validation
     *
     * @throws \Statamic\Exceptions\PublishException
     */
    protected function initialValidation()
    {
        $rules = ['fields.title' => 'required'];

        if (! $this->request->input('extra.is_home')) {
            $rules['slug'] = "required|alpha_dash|page_uri_exists:{$this->request->input('extra.parent_url')},{$this->request->uuid}";
        }

        $this->validate($rules, [], [
            'fields.title' => $this->getTitleDisplayName(),
            'slug' => trans('cp.slug')
        ]);
    }

    /**
     * Prepare the content object
     *
     * Retrieve, update, and/or create a Page, depending on the situation.
     */
    protected function prepare()
    {
        $this->parent_uri = $this->request->input('extra.parent_url');
        $this->slug = $this->getSubmittedSlug();

        if ($this->isNew()) {
            $this->prepForNewPage();
        } else {
            $this->prepForExistingPage();
        }
    }

    /**
     * Prepare a new page
     *
     * @return void
     */
    private function prepForNewPage()
    {
        $this->id = Helper::makeUuid();

        $uri = URL::assemble($this->parent_uri, $this->slug);
        $locale = $this->request->input('locale');

        $this->content = Page::create($uri)->get();

        if ($locale !== default_locale()) {
            $this->content->published(false);
            $this->content = $this->content->in($locale)->get();
        }

        $this->content->published($this->request->input('status'));

        $this->content->order($this->getNextPageOrderKey());

        $this->content->fieldset($this->request->input('fieldset'));

        $this->content->syncOriginal();
    }

    /**
     * Prepare an existing page
     *
     * @return void
     */
    private function prepForExistingPage()
    {
        $this->id = $this->request->input('uuid');

        $this->content = Page::find($this->id)->in($this->locale)->get();

        $this->content->published($this->getSubmittedStatus());
    }

    /**
     * Get the next available page order key
     *
     * @return null|string
     */
    protected function getNextPageOrderKey()
    {
        return $this->content->parent()->children(1)->count() + 1;
    }

    /**
     * Get the slug from the submission
     *
     * @return string
     */
    protected function getSubmittedSlug()
    {
        if ($this->request->input('extra.is_home')) {
            return null;
        } else {
            return parent::getSubmittedSlug();
        }
    }
}
