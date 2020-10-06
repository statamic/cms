<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Exception;
use Facades\Statamic\View\Cascade;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\CpController;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Throwable;

class EntryPreviewController extends CpController
{
    public function show()
    {
        return view('statamic::entries.preview');
    }

    public function edit(Request $request, $collection, $entry)
    {
        $this->authorize('view', $entry);

        $fields = $entry->blueprint()
            ->fields()
            ->addValues($request->input('preview', []))
            ->process();

        foreach (array_except($fields->values()->all(), ['slug']) as $key => $value) {
            $entry->setSupplement($key, $value);
        }

        return $this->getEntryResponse($request, $entry)->getContent();
    }

    public function create(Request $request, $collection, $site)
    {
        $this->authorize('create', [EntryContract::class, $collection]);

        $fields = $collection->entryBlueprint($request->blueprint)
            ->fields()
            ->addValues($preview = $request->preview)
            ->process();

        $values = array_except($fields->values()->all(), ['slug']);

        $entry = Entry::make()
            ->slug($preview['slug'] ?? 'slug')
            ->collection($collection)
            ->locale($site->handle())
            ->data($values);

        if ($collection->dated()) {
            $entry->date($preview['date'] ?? now()->format('Y-m-d-Hi'));
        }

        return $this->getEntryResponse($request, $entry)->getContent();
    }

    protected function getEntryResponse($request, $entry)
    {
        $url = $request->amp ? $entry->ampUrl() : $entry->absoluteUrl();

        $subrequest = Request::createFromBase(SymfonyRequest::create($url));

        $subrequest->headers->set('X-Statamic-Live-Preview', true);

        app()->instance('request', $subrequest);
        Facade::clearResolvedInstance('request');
        Cascade::withRequest($subrequest);

        try {
            $response = $entry->toLivePreviewResponse($subrequest, $request->extras);
        } catch (Exception $e) {
            app(ExceptionHandler::class)->report($e);
            $response = app(ExceptionHandler::class)->render($subrequest, $e);
        } catch (Throwable $e) {
            app(ExceptionHandler::class)->report($e = new FatalThrowableError($e));
            $response = app(ExceptionHandler::class)->render($subrequest, $e);
        }

        app()->instance('request', $request);
        Facade::clearResolvedInstance('request');
        Cascade::withRequest($request);

        return $response;
    }
}
