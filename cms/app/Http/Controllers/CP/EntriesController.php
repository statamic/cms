<?php

namespace Statamic\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use Statamic\API\Entry;
use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\API\Collection;
use Statamic\Presenters\PaginationPresenter;

/**
 * Controller for the entry listing
 */
class EntriesController extends CpController
{
    /**
     * List folders containing entries
     *
     * The view for /cp/entries
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return redirect()->route('entries');
    }

    /**
     * List the entries for a collection
     *
     * The view for /cp/entries/{collection}
     *
     * @param string $collection
     * @return \Illuminate\View\View
     */
    public function show($collection)
    {
        $this->access("collections:$collection:edit");

        if (! Collection::handleExists($collection)) {
            abort(404, "Collection [$collection] does not exist.");
        }

        $collection = Collection::whereHandle($collection);

        $sort = 'title';
        $sort_order = 'asc';
        if ($collection->order() === 'date') {
            $sort = 'datestamp';
            $sort_order = $collection->get('sort_dir', 'desc');
        } elseif ($collection->order() === 'number') {
            $sort = 'order';
            $sort_order = $collection->get('sort_dir', 'asc');
        }

        $reorderable = $collection->order() === 'number' && $collection->count() <= Config::get('cp.pagination_size');

        return view('entries.index', [
            'collection' => $collection,
            'title' => $collection->title(),
            'sort' => $sort,
            'sort_order' => $sort_order,
            'reorderable' => $reorderable,
            'new_entry_link' => route('entry.create', $collection->path())
        ]);
    }

    /**
     * Get the entries as JSON
     *
     * Used for injecting into the Vue templates
     *
     * @param string $folder
     * @return mixed
     */
    public function get($folder)
    {
        $collection = Collection::whereHandle($folder);

        // Grab the entries from the collection.
        $entries = $collection->entries()->values();

        if ($locale = request('locale')) {
            $entries = $entries->localize($locale);
        }

        if (! request('drafts', true)) {
            $entries = $entries->removeUnpublished();
        }

        // The table Vue component uses a "checked" value for checkboxes. We'll initialize
        // them all to an unchecked state so Vue can have an initial value to work with.
        $entries->supplement('checked', function() {
            return false;
        });

        // Set the default/fallback sort order
        $sort = 'title';
        $sortOrder = 'asc';

        // Set up the columns that the Vue component will be expecting. A developer may customize these
        // columns in the collection's configuration file, but if left blank we will set the defaults.
        $columns = array_get($collection->data(), 'columns', ['title', 'slug']);

        // Special handling for date based collections.
        if ($collection->order() === 'date') {
            // Add a formatted date to each entry that will be used as the the displayed value.
            // We name this date_col_header to make it clearer in the translation files what
            // you are actually translating. The field names end up as the header string.
            $format = Config::get('cp.date_format');
            $entries->supplement('date_col_header', function ($entry) use ($format) {
                return $entry->date()->format($format);
            });

            // Add a date column, which is displayed using the above formatted and
            // supplemented date, but will actually use the datestamp for sorting.
            $columns[] = ['label' => 'date_col_header', 'field' => 'datestamp'];

            $sort = 'datestamp';
            $sortOrder = 'desc';
        }

        // Special handling for number based collections.
        if ($collection->order() === 'number') {
            $columns[] = 'order';
        }

        // Custom sorting will override anything predefined.
        if ($customSort = $this->request->sort) {
            $sort = $customSort;
        }
        if ($customOrder = $this->request->order) {
            $sortOrder = $customOrder;
        }

        // If we've requested datestamp, we actually want date.
        if ($sort === 'datestamp') {
            $sort = 'date';
        }

        // Perform the sort!
        $entries = $entries->multisort("$sort:$sortOrder");

        // Set up the paginator, since we don't want to display all the entries.
        $totalEntryCount = $entries->count();
        $perPage = Config::get('cp.pagination_size');
        $currentPage = (int) $this->request->page ?: 1;
        $offset = ($currentPage - 1) * $perPage;
        $entries = $entries->slice($offset, $perPage);
        $paginator = new LengthAwarePaginator($entries, $totalEntryCount, $perPage, $currentPage);

        $items = $entries->toArray();

        // Adjust the edit urls to add the locales
        if ($locale !== default_locale()) {
            foreach ($items as &$item) {
                $item['edit_url'] = $item['edit_url'] . '?locale=' . $locale;
            }
        }

        return [
            'columns' => $columns,
            'items' => $items,
            'pagination' => [
                'totalItems' => $totalEntryCount,
                'itemsPerPage' => $perPage,
                'totalPages'    => $paginator->lastPage(),
                'currentPage'   => $paginator->currentPage(),
                'prevPage'      => $paginator->previousPageUrl(),
                'nextPage'      => $paginator->nextPageUrl(),
                'segments'      => array_get($paginator->render(new PaginationPresenter($paginator)), 'segments')
            ]
        ];
    }

    /**
     * Delete an entry
     *
     * @return array
     */
    public function delete()
    {
        $ids = Helper::ensureArray($this->request->input('ids'));

        $entries = [];

        // Gather all the entries to be deleted, but before deleting, check if
        // there are any authorization issues before attempting to continue.
        foreach ($ids as $id) {
            $entry = Entry::find($id);
            $entries[] = $entry;

            $this->authorize("collections:{$entry->collection()->path()}:delete");
        }

        // All good? Commence deleting.
        foreach ($entries as $entry) {
            $entry->delete();
        }

        return ['success' => true];
    }

    public function reorder()
    {
        $ids = $this->request->input('ids');

        foreach ($ids as $key => $id) {
            $entry = Entry::find($id);

            $entry->order($key + 1);

            $entry->save();
        }

        return ['success' => true];
    }
}
