<?php

namespace Statamic\Data\Entries;

use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\API\Config;
use Statamic\API\Folder;
use Statamic\API\Search;
use Statamic\API\Fieldset;
use Statamic\Data\DataFolder;
use Statamic\API\Entry as EntryAPI;
use Statamic\Events\Data\CollectionDeleted;
use Statamic\API\Collection as CollectionAPI;
use Statamic\Contracts\Data\Entries\Collection as CollectionContract;

class Collection extends DataFolder implements CollectionContract
{
    /**
     * @var \Statamic\Data\EntryCollection
     */
    protected $entries;

    /**
     * @var \Carbon\Carbon
     */
    protected $last_modified;

    /**
     * @return int
     */
    public function count()
    {
        return EntryAPI::countWhereCollection($this->path());
    }

    /**
     * @return \Statamic\Data\EntryCollection
     */
    public function entries()
    {
        if ($this->entries) {
            return $this->entries;
        }

        if (! $entries = EntryAPI::whereCollection($this->path())) {
            $entries = collect_entries();
        }

        switch ($this->order()) {
            case 'number':
                $entries = $entries->multisort('order:asc');
                break;
            case 'date':
                $entries = $entries->multisort('date:desc');
                break;
            default:
                $entries = $entries->multisort('title:asc');
        }

        return $this->entries = $entries;
    }

    /**
     * @param string                         $key
     * @param \Statamic\Contracts\Data\Entry $entry
     */
    public function addEntry($key, $entry)
    {
        $this->entries->put($key, $entry);
    }

    /**
     * @param string $key
     */
    public function removeEntry($key)
    {
        $this->entries->pull($key);
    }

    /**
     * @return \Carbon\Carbon
     */
    public function lastModified()
    {
        $date = null;

        foreach ($this->entries() as $entry) {
            $modified = $entry->lastModified();

            if ($date) {
                if ($modified->gt($date)) {
                    $date = $modified;
                }
            } else {
                $date = $modified;
            }
        }

        return $date;
    }

    /**
     * @return mixed
     */
    public function save()
    {
        CollectionAPI::save($this);

        return $this; // TODO: Test
    }

    /**
     * Get the collection order
     *
     * @return string
     */
    public function order()
    {
        $order = $this->get('order');

        if (in_array($order, ['number', 'numeric', 'numerical', 'numbers', 'numbered'])) {
            return 'number';
        }

        if ($order === 'date') {
            return 'date';
        }

        return 'alphabetical';
    }

    /**
     * Delete the folder
     *
     * @return mixed
     */
    public function delete()
    {
        // TODO: Re-implement correctly.

        app('stache')->store('collections')->removeItem($this->path());
        app('files')->delete(
            app('stache')->store('collections')->directory() . $this->path() . '.yaml'
        );

        // event(new CollectionDeleted($this->path()));
    }

    /**
     * Get the URL to edit this in the CP
     *
     * @return string
     */
    public function editUrl()
    {
        return cp_route('collections.edit', $this->path());
    }

    /**
     * Get the URL to create a new Entry in this Collection inn the CP
     *
     * @return string
     */
    public function createEntryUrl()
    {
        return cp_route('entry.create', $this->path());
    }

    /**
     * Get or set the route definition
     *
     * @return string
     */
    public function route($route = null)
    {
        if (is_null($route)) {
            return $this->get('route');
        }

        $this->set('route', $route);

        return $this;
    }

    /**
     * Get or set the fieldset
     *
     * @param string|null|bool
     * @return Statamic\Fields\Fieldset
     */
    public function fieldset($fieldset = null)
    {
        if (! is_null($fieldset)) {
            $this->set('fieldset', $fieldset);
        }

        if ($fieldset === false) {
            $this->set('fieldset', null);
        }

        return Fieldset::get([
            $this->get('fieldset'),
            config('statamic.theming.fieldsets.entry'),
            config('statamic.theming.fieldsets.default')
        ]);
    }

    public function blueprints()
    {
        return collect($this->get('blueprints', []))->map(function ($blueprint) {
            return \Facades\Statamic\Fields\BlueprintRepository::find($blueprint);
        });
    }

    public function blueprint()
    {
        return $this->blueprints()->first();
    }

    // TODO: Deprecate path
    public function handle($handle = null)
    {
        return $this->path($handle);
    }

    public function queryEntries()
    {
        return EntryAPI::query()->where('collection', $this->handle());
    }

    public function searchIndex()
    {
        if (! $index = $this->get('search_index')) {
            return null;
        }

        return Search::index($index);
    }

    public function hasSearchIndex()
    {
        return $this->searchIndex() !== null;
    }
}
