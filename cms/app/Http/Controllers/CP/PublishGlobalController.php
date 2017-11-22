<?php

namespace Statamic\Http\Controllers;

use Statamic\API\GlobalSet;
use Illuminate\Http\Request;

class PublishGlobalController extends PublishController
{
    /**
     * Edit the global.
     *
     * @param  Request  $request
     * @param  string  $slug
     * @return void
     */
    public function edit(Request $request, $slug)
    {
        $this->authorize("globals:$slug:edit");

        $locale = $this->request->query('locale', site_locale());

        if (! $global = GlobalSet::whereHandle($slug)) {
            return redirect()->route('globals.index')->withErrors('No content found.');
        }

        $id = $global->id();

        $global = $global->in($locale)->get();

        $extra = [
            'default_slug' => $slug,
            'env' => datastore()->getEnvInScope('globals.'.$slug)
        ];

        $data = $this->populateWithBlanks($global);

        return view('publish', [
            'extra'             => $extra,
            'is_new'            => false,
            'content_data'      => $data,
            'content_type'      => 'global',
            'fieldset'          => $global->fieldset()->name(),
            'title'             => array_get($data, 'title', $global->title()),
            'uuid'              => $id,
            'uri'               => null,
            'url'               => null,
            'slug'              => $slug,
            'status'            => true,
            'locale'            => $locale,
            'is_default_locale' => $global->isDefaultLocale(),
            'locales'           => $this->getLocales($id),
            'suggestions'       => $this->getSuggestions($global->fieldset()),
        ]);
    }

    /**
     * Build the redirect.
     *
     * @param  Request  $request
     * @param  \Statamic\Data\Globals\GlobalSet  $global
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirect(Request $request, $global)
    {
        if ($request->continue) {
            return route('globals');
        }

        return route('globals.edit', [
            'slug' => $global->slug(),
        ]);
    }

    /**
     * Whether the user is authorized to publish the object.
     *
     * @param Request $request
     * @return bool
     */
    protected function canPublish(Request $request)
    {
        $slug = $request->input('extra.default_slug');

        return $request->user()->can("globals:$slug:edit");
    }
}
