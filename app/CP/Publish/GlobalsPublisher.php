<?php

namespace Statamic\CP\Publish;

use Statamic\API\GlobalSet;

class GlobalsPublisher extends Publisher
{
    /**
     * Prepare the content object
     *
     * Retrieve, update, and/or create a Global, depending on the situation.
     */
    protected function prepare()
    {
        if ($this->isNew()) {
            $this->prepForNewGlobal();
        } else {
            $this->prepForExistingGlobal();
        }
    }

    /**
     * Prepare a brand new global
     *
     * @return void
     */
    protected function prepForNewGlobal()
    {
        $this->slug = $this->getSubmittedSlug();

        $this->content = GlobalSet::create($this->slug)->get();
    }

    /**
     * Prepare an existing global
     *
     * @throws \Exception
     */
    private function prepForExistingGlobal()
    {
        $this->id = $this->request->input('uuid');

        $this->content = GlobalSet::find($this->id)->in($this->locale)->get();

        // Maintain the fieldset
        $this->fields['fieldset'] = $this->content->get('fieldset');
    }

    /**
     * Perform initial validation
     *
     * @throws \Statamic\Exceptions\PublishException
     */
    protected function initialValidation()
    {
        //
    }
}
