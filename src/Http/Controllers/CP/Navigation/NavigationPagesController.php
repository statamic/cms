<?php

namespace Statamic\Http\Controllers\CP\Navigation;

use Illuminate\Http\Request;
use Statamic\Facades\Nav;
use Statamic\Fields\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class NavigationPagesController extends CpController
{
    public function update(Request $request, $nav)
    {
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
