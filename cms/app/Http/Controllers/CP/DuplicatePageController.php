<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Page;
use Illuminate\Http\Request;

class DuplicatePageController extends CpController
{
    public function store(Request $request)
    {
        $this->authorize('pages:create');

        if (! $page = Page::find($request->id)) {
            throw new \Exception('Page does not exist.');
        }

        $data = $page->data();
        unset($data['id']);

        $duplicatePage = Page::create($this->getDuplicateUri($page->uri()))
            ->order($page->order())
            ->published(false)
            ->ensureId()
            ->get();

        $id = $duplicatePage->id();

        foreach ($page->locales() as $locale) {
            $localized = $page->dataForLocale($locale);
            $localized['id'] = $id;

            $duplicatePage->dataForLocale($locale, $localized);
        }

        $duplicatePage->save();

        $this->success(t('page_created'));

        return ['redirect' => $duplicatePage->editUrl()];
    }

    /**
     * Get the URI for the duplicate page.
     *
     * @param  string  $uri      The URI to build a new URI from.
     * @param  integer $attempt  The current attempt at building a URI since this used recursively.
     * @return string
     */
    private function getDuplicateUri($uri, $attempt = 1)
    {
        // If it's not the first attempt, we'll erase the previously suffixed number and dash.
        if ($attempt > 1) {
            $uri = substr($uri, 0, -strlen($attempt-1) - 1);
        }

        $uri .= '-' . $attempt;

        // If the URI we've just built already exists, we'll try again, recursively.
        if (Page::uriExists($uri)) {
            $uri = $this->getDuplicateUri($uri, $attempt + 1);
        }

        return $uri;
    }
}
