<?php

namespace Statamic\Http\Controllers\CP\Utilities;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Facades\Utility;
use Statamic\Http\Controllers\CP\CpController;

class UtilitiesController extends CpController
{
    public function index()
    {
        return Inertia::render('utilities/Index', [
            'utilities' => Utility::authorized()->sortBy->title()->map(fn ($utility) => [
                'title' => $utility->title(),
                'description' => $utility->description(),
                'icon' => $utility->icon(),
                'url' => $utility->url(),
            ])->values(),
        ]);
    }

    public function show(Request $request)
    {
        $utility = Utility::findBySlug($this->getUtilityHandle($request));

        if ($view = $utility->view()) {
            return Inertia::render('utilities/Show', [
                'html' => $this->renderView($view, $utility->viewData($request)),
            ]);
        }

        throw new \Exception("Utility [{$utility->handle()}] has not been provided with an action or view.");
    }

    private function renderView($view, $data)
    {
        $html = view($view, $data)->render();

        return $this->extractTemplateFromLayout($html);
    }

    private function extractTemplateFromLayout($html)
    {
        // If it doesn't contain the layout's #statamic div, it probably didn't extend the layout.
        if (! str_contains($html, 'id="statamic"')) {
            return $html;
        }

        $dom = new \DOMDocument();
        @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        if ($statamicDiv = $dom->getElementById('statamic')) {
            $content = '';
            foreach ($statamicDiv->childNodes as $node) {
                // Skip the blade-title div
                if ($node->nodeType === XML_ELEMENT_NODE && $node->getAttribute('id') === 'blade-title') {
                    continue;
                }
                $content .= $dom->saveHTML($node);
            }

            return $content;
        }

        return $html;
    }

    private function getUtilityHandle($request)
    {
        preg_match('/\/utilities\/([^\/]+)/', $request->url(), $matches);

        return $matches[1];
    }
}
