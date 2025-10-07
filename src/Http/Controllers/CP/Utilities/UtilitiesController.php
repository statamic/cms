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
            return view($view, $utility->viewData($request));
        }

        throw new \Exception("Utility [{$utility->handle()}] has not been provided with an action or view.");
    }

    private function getUtilityHandle($request)
    {
        preg_match('/\/utilities\/([^\/]+)/', $request->url(), $matches);

        return $matches[1];
    }
}
