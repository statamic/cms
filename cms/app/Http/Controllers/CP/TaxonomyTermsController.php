<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\API\Taxonomy;
use Statamic\API\Term;
use Statamic\Presenters\PaginationPresenter;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Controller for the taxonomies listing
 */
class TaxonomyTermsController extends CpController
{
    /**
     * List the taxonomies for a group
     *
     * @param string $group
     * @return \Illuminate\View\View
     */
    public function show($group)
    {
        $this->access("taxonomies:$group:edit");

        if (! Taxonomy::whereHandle($group)) {
            abort(404, "Taxonomy group [$group] does not exist.");
        }

        $title = Taxonomy::whereHandle($group)->title();

        return view('taxonomies.terms', [
            'title' => $title,
            'group' => $group,
            'group_title' => $title,
            'new_taxonomy_link' => route('taxonomy.create', compact('group'))
        ]);
    }

    /**
     * Get the taxonomies as JSON
     *
     * Used for injecting into the Vue components
     *
     * @param string $folder
     * @return mixed
     */
    public function get($folder)
    {
        $this->access("taxonomies:$folder:edit");

        $taxonomy = Taxonomy::whereHandle($folder);

        // Grab the terms from the taxonomy.
        $terms = $taxonomy->terms()->values();

        // The table Vue component uses a "checked" value for checkboxes. We'll initialize
        // them all to an unchecked state so Vue can have an initial value to work with.
        $terms->supplement('checked', function() {
            return false;
        });

        // Set up the columns that the Vue component will be expecting. A developer may customize these
        // columns in the taxonomy's configuration file, but if left blank we will set the defaults.
        $columns = array_get($taxonomy->data(), 'columns', ['title', 'slug', 'count']);

        // Set the default/fallback sort order
        $sort = 'title';
        $sortOrder = 'asc';

        // Custom sorting will override anything predefined.
        if ($customSort = $this->request->sort) {
            $sort = $customSort;
        }
        if ($customOrder = $this->request->order) {
            $sortOrder = $customOrder;
        }

        // Perform the sort!
        $terms = $terms->multisort("$sort:$sortOrder");

        // Set up the paginator, since we don't want to display all the assets.
        $totalTermCount = $terms->count();
        $perPage = Config::get('cp.pagination_size');
        $currentPage = (int) $this->request->page ?: 1;
        $offset = ($currentPage - 1) * $perPage;
        $terms = $terms->slice($offset, $perPage);
        $paginator = new LengthAwarePaginator($terms, $totalTermCount, $perPage, $currentPage);

        return [
            'items' => $terms,
            'columns' => $columns,
            'pagination' => [
                'totalItems' => $totalTermCount,
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
     * Delete a taxonomy
     *
     * @return array
     */
    public function delete()
    {
        $ids = Helper::ensureArray($this->request->input('ids'));

        $terms = [];

        // Gather all the terms to be deleted, but before deleting, check if
        // there are any authorization issues before attempting to continue.
        foreach ($ids as $id) {
            $term = Term::find($id);
            $terms[] = $term;

            $this->authorize("taxonomies:{$term->taxonomy()->path()}:delete");
        }

        // All good? Commence deleting.
        foreach ($terms as $term) {
            $term->delete();
        }

        return ['success' => true];
    }
}
