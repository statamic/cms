<?php

namespace Statamic\CP\Publish;

use Statamic\API\Helper;
use Statamic\API\Term;

class TaxonomyPublisher extends Publisher
{
    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $group;

    public function publish()
    {
        $this->group = $this->request->input('extra.taxonomy');

        return parent::publish();
    }

    /**
     * Perform initial validation
     *
     * @throws \Statamic\Exceptions\PublishException
     */
    protected function initialValidation()
    {
        $rules = [
            'fields.title' => 'required',
            'slug' => 'required|alpha_dash'
        ];

        $messages = [];

        $this->validate($rules, $messages, [
            'fields.title' => $this->getTitleDisplayName(),
            'slug' => 'Slug'
        ]);
    }

    /**
     * Prepare the content object
     *
     * Retrieve, update, and/or create an Entry, depending on the situation.
     */
    protected function prepare()
    {
        $this->slug = $this->getSubmittedSlug();

        if ($this->isNew()) {
            $this->prepForBrandNewTaxonomy();
        } else {
            $this->prepForExistingTaxonomy();
        }
    }

    /**
     * Prepare a brand new term
     *
     * @return void
     */
    private function prepForBrandNewTaxonomy()
    {
        $this->id = Helper::makeUuid();

        $this->content = Term::create($this->slug)
            ->taxonomy($this->group)
            ->published($this->getSubmittedStatus())
            ->order($this->getSubmittedOrderKey())
            ->get();
    }

    /**
     * Prepare an existing term
     *
     * @return void
     */
    private function prepForExistingTaxonomy()
    {
        $this->id = $this->request->input('uuid');

        $this->slug = $this->getSubmittedSlug();

        $this->content = Term::find($this->id)->in($this->locale)->get();

        if (! $this->isLocalized()) {
            // Only the default locale can have its status and order modified
            $this->content->published($this->getSubmittedStatus());
            $this->content->order($this->getSubmittedOrderKey());
        }
    }
}
