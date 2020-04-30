<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Illuminate\Http\Request;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\CP\Taxonomies\Term as TermResource;

class TermRevisionsController extends CpController
{
    public function index(Request $request, $taxonomy, $term)
    {
        $revisions = $term
            ->revisions()
            ->reverse()
            ->prepend($this->workingCopy($term))
            ->filter();

        // The first non manually created revision would be considered the "current"
        // version. It's what corresponds to what's in the content directory.
        optional($revisions->first(function ($revision) {
            return $revision->action() != 'revision';
        }))->attribute('current', true);

        return $revisions
            ->groupBy(function ($revision) {
                return $revision->date()->clone()->startOfDay()->format('U');
            })->map(function ($revisions, $day) {
                return compact('day', 'revisions');
            })->reverse()->values();
    }

    public function store(Request $request, $taxonomy, $term)
    {
        $term->createRevision([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);

        return new TermResource($term);
    }

    public function show(Request $request, $taxonomy, $term, $site, $revision)
    {
        $term = $term->makeFromRevision($revision);

        // TODO: Most of this is duplicated with EntriesController@edit. DRY it off.

        $blueprint = $term->blueprint();

        $fields = $blueprint
            ->fields()
            ->addValues($term->data())
            ->preProcess();

        $values = array_merge($fields->values()->all(), [
            'title' => $term->get('title'),
            'slug' => $term->slug(),
        ]);

        return [
            'title' => $term->value('title'),
            'editing' => true,
            'actions' => [
                'save' => $term->updateUrl(),
                'publish' => $term->publishUrl(),
                'unpublish' => $term->unpublishUrl(),
                'revisions' => $term->revisionsUrl(),
                'restore' => $term->restoreRevisionUrl(),
                'createRevision' => $term->createRevisionUrl(),
            ],
            'values' => $values,
            'meta' => $fields->meta(),
            'taxonomy' => $this->taxonomyToArray($term->taxonomy()),
            'blueprint' => $blueprint->toPublishArray(),
            'readOnly' => User::fromUser($request->user())->cant('edit', $term),
            'published' => $term->published(),
            'locale' => $term->locale(),
            'localizations' => $term->taxonomy()->sites()->map(function ($handle) use ($term) {
                $localized = $term->in($handle);
                $exists = $localized !== null;

                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $term->locale(),
                    'exists' => $exists,
                    'root' => $exists ? $localized->isRoot() : false,
                    'origin' => $exists ? $localized->id() === optional($term->origin())->id() : null,
                    'published' => $exists ? $localized->published() : false,
                    'url' => $exists ? $localized->editUrl() : null,
                ];
            })->all(),
        ];
    }

    protected function workingCopy($term)
    {
        if ($term->published()) {
            return $term->workingCopy();
        }

        return $term
            ->makeWorkingCopy()
            ->date($term->lastModified())
            ->user($term->lastModifiedBy());
    }

    protected function taxonomyToArray($taxonomy)
    {
        return [
            'title' => $taxonomy->title(),
            'url' => cp_route('taxonomies.show', $taxonomy->handle()),
        ];
    }
}
