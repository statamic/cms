<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\API\Site;
use Statamic\API\Entry;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class EntryPreviewController extends CpController
{
    public function edit(Request $request, $collection, $id, $slug, $site)
    {
        if (! Site::get($site)) {
            return $this->pageNotFound();
        }

        if (! $entry = Entry::find($id)) {
            return $this->pageNotFound();
        }

        if (! $entry->collection()->sites()->contains($site)) {
            return $this->pageNotFound();
        }

        $entry = $entry->in($site);

        $this->authorize('preview', $entry);

        foreach ($request->input('preview', []) as $key => $value) {
            $entry->setSupplement($key, $value);
        }

        return $this->getEntryResponse($request, $entry)->getContent();
    }

    protected function getEntryResponse($request, $entry)
    {
        $kernel = app()->make(HttpKernel::class);

        $url = $request->amp ? $entry->ampUrl() : $entry->absoluteUrl();

        $response = $kernel->handle(
            $subrequest = Request::createFromBase(SymfonyRequest::create($url))
        );

        $kernel->terminate($subrequest, $response);

        app()->instance('request', $request);

        return $response;
    }
}
