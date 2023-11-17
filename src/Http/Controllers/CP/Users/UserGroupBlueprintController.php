<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Statamic\Facades\UserGroup;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Fields\ManagesBlueprints;

class UserGroupBlueprintController extends CpController
{
    use ManagesBlueprints;

    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure fields');
    }

    public function edit()
    {
        $blueprint = UserGroup::make()->blueprint();

        return view('statamic::usergroups.blueprints.edit', [
            'blueprint' => $blueprint,
            'blueprintVueObject' => $this->toVueObject($blueprint),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate(['sections' => 'array']);

        $this->updateBlueprint($request, UserGroup::make()->blueprint());
    }
}
