<?php

namespace Statamic\CP\Publish;

use Carbon\Carbon;
use Statamic\API\Entry;
use Statamic\API\Helper;

class EntryPublisher extends Publisher
{
    /**
     * @var string
     */
    protected $collection;

    /**
     * Publish the entry
     *
     * @return \Statamic\Data\Entry
     */
    public function publish()
    {
        $this->collection = $this->request->input('extra.collection');

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
            'slug' => "required|alpha_dash|entry_slug_exists:{$this->collection},{$this->request->uuid}",
        ];

        if ($this->getEntryOrderType() === 'date') {
            // 24 hour validation, hat tip to:
            // http://www.mkyong.com/regular-expressions/how-to-validate-time-in-24-hours-format-with-regular-expression/
            $rules['extra.datetime'] = ['required', 'regex:/^\d{4}-\d{2}-\d{2}(?: ([01][0-9]|2[0-3]):[0-5][0-9])?$/'];
        }

        $messages = [
            'extra.datetime.regex' => 'The Date/time field must be a valid 24 hour time (HH:MM).'
        ];

        $this->validate($rules, $messages, [
            'fields.title' => $this->getTitleDisplayName(),
            'slug' => 'Slug',
            'extra.datetime' => 'Date/Time'
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
            $this->prepForNewEntry();
        } else {
            $this->prepForExistingEntry();
        }
    }

    /**
     * Prepare a new entry
     *
     * @return void
     */
    private function prepForNewEntry()
    {
        $this->id = Helper::makeUuid();
        $locale = $this->request->input('locale');

        $this->content = Entry::create($this->slug)
            ->collection($this->collection)
            ->get();

        if ($locale !== default_locale()) {
            $this->content->set('title', $this->slug);
            $this->content->published(false);
            $this->content = $this->content->in($locale)->get();
        }

        $this->content->published($this->getSubmittedStatus());

        $this->content->order(
            $this->getSubmittedOrderKey() ?: $this->getNewEntryOrderKey()
        );
    }

    /**
     * Prepare an existing entry
     *
     * @return void
     */
    private function prepForExistingEntry()
    {
        $this->id = $this->request->input('uuid');

        $this->content = Entry::find($this->id)->in($this->locale)->get();

        $this->content->published($this->getSubmittedStatus());

        // Only the default locale can have its order modified
        if (! $this->isLocalized()) {
            // If no order was submitted (in the case of numeric
            // entries), we want to get the existing order key.
            if (! $order = $this->getSubmittedOrderKey()) {
                $order = $this->content->order();
            }

            $this->content->order($order);
        }
    }

    protected function getSubmittedOrderKey()
    {
        // If it's not a date, you can't choose the order of an entry while publishing.
        if ($this->getEntryOrderType() !== 'date') {
            return null;
        }

        $date = $this->request->input('extra.datetime');

        // If there's a time, adjust the format into a datetime order string.
        if (strlen($date) > 10) {
            $date = str_replace(':', '', $date);
            $date = str_replace(' ', '-', $date);
        }

        return $date;
    }

    private function getEntryOrderType()
    {
        // Get the entry order type from either the content if it exists, or from the POST for a new entry.
        return ($this->content)
            ? $this->content->orderType()
            : $this->request->input('extra.order_type');
    }

    private function getNewEntryOrderKey()
    {
        $order_type = $this->getEntryOrderType();

        if ($order_type === 'date') {
            return Carbon::now()->format('Y-m-d');
        }

        if ($order_type === 'number') {
            return Entry::whereCollection($this->collection)->count() + 1;
        }
    }
}
