<?php

namespace Statamic\Http\Controllers\CP\Navigation;

use Illuminate\Http\Request;
use Statamic\Facades\Nav;
use Statamic\Fields\Blueprint;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Structures\Page;

class NavigationPagesController extends CpController
{
    /**
     * The "create" action, which doesn't actually render the page, but rather
     * returns values, meta, etc for the page editor component.
     */
    public function create(Request $request, $nav)
    {
        $nav = Nav::find($nav);

        $blueprint = $nav->blueprint();

        $page = (new Page)
            ->setTree($nav->in($request->site))
            ->setEntry($request->entry);

        [$values, $meta] = $this->extractValuesAndMeta($page, $blueprint);

        if ($entry = $page->entry()) {
            [$originValues, $originMeta] = $this->extractValuesAndMeta($entry, $blueprint);
        }

        return [
            'values' => $values,
            'meta' => $meta,
            'originValues' => $originValues ?? null,
            'originMeta' => $originMeta ?? null,
            'localizedFields' => $this->getLocalizedFields($page),
            'syncableFields' => $this->getSyncableFields($nav, $entry),
        ];
    }

    /**
     * The "edit" action, which doesn't actually render the page, but rather
     * returns values, meta, etc for the the page editor component.
     */
    public function edit(Request $request, $nav, $page)
    {
        $nav = Nav::find($nav);

        $blueprint = $nav->blueprint();

        $page = $nav->in($request->site)->find($page);

        [$values, $meta] = $this->extractValuesAndMeta($page, $blueprint);

        if ($entry = $page->entry()) {
            [$originValues, $originMeta] = $this->extractValuesAndMeta($entry, $blueprint);
        }

        return [
            'values' => $values,
            'meta' => $meta,
            'originValues' => $originValues ?? null,
            'originMeta' => $originMeta ?? null,
            'localizedFields' => $this->getLocalizedFields($page),
            'syncableFields' => $this->getSyncableFields($nav, $entry),
        ];
    }

    private function getLocalizedFields($page)
    {
        $fields = $page->pageData()->keys();

        if ($page->hasCustomTitle()) {
            $fields[] = 'title';
        }

        if ($page->hasCustomUrl()) {
            $fields[] = 'url';
        }

        return $fields;
    }

    private function getSyncableFields($nav, $entry)
    {
        $navFields = $nav->blueprint()->fields()->all()->keys();

        if (! $entry) {
            return $navFields;
        }

        return $entry->blueprint()
            ->fields()->all()->keys()
            ->intersect($navFields)->values()
            ->flip()->forget('url')
            ->flip()->all();
    }

    private function extractValuesAndMeta($page, $blueprint)
    {
        $values = $page instanceof Page
            ? $this->getPageValues($page)
            : $this->getEntryValues($page);

        $fields = $blueprint
            ->ensureField('title', [])
            ->ensureField('url', [])
            ->fields()
            ->addValues($values)
            ->preProcess();

        $values = $fields->values();

        return [$values->all(), $fields->meta()];
    }

    private function getPageValues($page)
    {
        $entryValues = ($entry = $page->entry())
            ? $this->getEntryValues($entry)
            : collect();

        return collect($entryValues)
            ->merge($page->pageData())
            ->merge([
                'title' => $page->title(),
                'url' => $page->reference() ? null : $page->url(),
            ])->all();
    }

    private function getEntryValues($entry)
    {
        // The values should only be data merged with the origin data.
        // We don't want injected collection values, which $entry->values() would have given us.
        $target = $entry;
        $values = $target->data();
        while ($target->hasOrigin()) {
            $target = $target->origin();
            $values = $target->data()->merge($values);
        }

        return $values->all();
    }

    /**
     * The "update" action, which doesn't actually update the page, but just
     * validates it. The Vue component will "save" the data back into the
     * component, where all edited pages will be submitted together.
     */
    public function update(Request $request, $nav)
    {
        $request->validate(['type' => 'required|in:url,entry']);

        $nav = Nav::find($nav);

        $blueprint = $this->ensureFields($nav->blueprint(), $request);

        $blueprint->fields()
            ->addValues($request->values)
            ->validator()
            ->withRules($this->extraRules($request))
            ->validate();
    }

    private function ensureFields(Blueprint $blueprint, $request)
    {
        // Add fields so that the validation rules will display with the correct names
        if ($request->type === 'url') {
            $blueprint
                ->ensureField('title', ['display' => __('Title')])
                ->ensureField('url', ['display' => __('URL')]);
        }

        return $blueprint;
    }

    private function extraRules($request)
    {
        return $request->type === 'url' ? [
            'title' => ['required_without:url'],
            'url' => ['required_without:title'],
        ] : [];
    }
}
