<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Statamic\API\Site;
use Statamic\API\Entry;
use Statamic\CP\Column;
use Statamic\API\Blueprint;
use Illuminate\Http\Request;
use Statamic\API\Collection;
use Statamic\API\Preference;
use Statamic\Fields\Validation;
use Illuminate\Http\Resources\Json\Resource;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Events\Data\PublishBlueprintFound;
use Statamic\Http\Requests\FilteredSiteRequest;
use Statamic\Contracts\Data\Entries\Entry as EntryContract;

class TermsController extends CpController
{
    public function index(FilteredSiteRequest $request, $collection)
    {
        return [
            'data' => [
                [
                    'title' => 'Boots (hardcoded)',
                    'id' => 'boots',
                    'edit_url' => cp_route('taxonomies.terms.edit', ['tags', 'boots'])
                ],
                [
                    'title' => 'Cats (hardcoded)',
                    'id' => 'cats',
                    'edit_url' => cp_route('taxonomies.terms.edit', ['tags', 'cats'])
                ]
            ],
            'links' => [

            ],
            "meta" => [
                "current_page" => 1,
                "from" => 1,
                "last_page" => 1,
                "path" => "https:\/\/statamic3.test\/cp\/collections\/diary\/entries",
                "per_page" => "25",
                "to" => 2,
                "total" => 2,
                "filters" => [],
                "sortColumn" => "title",
                "columns" => [
                    [
                        "field" => "title",
                        "fieldtype" => "text",
                        "label" => "Title",
                        "listable" => true,
                        "visibleDefault" => true,
                        "visible" => true,
                        "sortable" => true,
                        "value" => null
                    ]
                ]
            ]
        ];
    }

    public function edit(Request $request, $taxonomy, $term)
    {
        //
    }
}
