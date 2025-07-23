<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class BlueprintController extends CpController
{
    use ManagesBlueprints;

    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure fields');
    }

    public function index()
    {
        $additional = Blueprint::getRenderableAdditionalNamespaces();

        return view('statamic::blueprints.index', [
            'additional' => $additional,
        ]);
    }
}
