<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\API\Page;
use Statamic\API\URL;
use Statamic\API\Fieldset;

/**
 * @todo  For the methods `create` and `edit`, not sure which is much cleaner,
 *        building the `submit_url` from there or building it from the view
 *        {{ route("{$content_type}.save") }}.
 */
class PublishPageController extends PublishController
{
    /**
     * Display the publish field.
     *
     * @param  Request  $request
     * @param  string  $url  The URL of the parent page.
     * @return \Illuminate\Contracts\View\View
     */
    public function create(Request $request, $url = '/')
    {
        $this->authorize('pages:create');

        if (! $parent = $this->page($url)) {
            return redirect(route('pages'))->withErrors("Page [$url] doesn't exist.");
        }

        $fieldset = $request->query('fieldset', $parent->fieldset()->name());

        return view('publish', [
            'title'             => t('create_page'),
            'uuid'              => null,
            'uri'               => null,
            'url'               => null,
            'slug'              => null,
            'extra'             => ['parent_url' => $url],
            'is_new'            => true,
            'content_type'      => 'page',
            'status'            => true,
            'is_default_locale' => true,
            'locale'            => $this->locale($request),
            'locales'           => $this->getLocales(),
            'fieldset'          => $fieldset,
            'content_data'      => $this->populateWithBlanks($fieldset),
            'taxonomies'        => $this->getTaxonomies(Fieldset::get($fieldset)),
            'suggestions'       => $this->getSuggestions(Fieldset::get($fieldset)),
        ]);
    }

    /**
     * Display the edit form for the page.
     *
     * @param  Request  $request
     * @param  string  $url  URL of the page to edit. No URI indicates the home page.
     * @return void
     */
    public function edit(Request $request, $url = '/')
    {
        $this->authorize('pages:edit');

        if (! $page = $this->page($url)) {
            return redirect()->route('pages')->withErrors('No page found.');
        }

        $locale = $this->locale($request);
        $page   = $page->in($locale)->get();
        $data   = $this->populateWithBlanks($page);

        return view('publish', [
            'is_new'            => false,
            'content_data'      => $data,
            'content_type'      => 'page',
            'fieldset'          => $page->fieldset()->name(),
            'title'             => array_get($data, 'title', $url),
            'title_display_name' => array_get($page->fieldset()->fields(), 'title.display', t('title')),
            'uuid'              => $page->id(),
            'uri'               => $page->uri(),
            'url'               => $page->url(),
            'slug'              => $page->slug(),
            'status'            => $page->published(),
            'locale'            => $locale,
            'is_default_locale' => $page->isDefaultLocale(),
            'locales'           => $this->getLocales($page->id()),
            'taxonomies'        => $this->getTaxonomies($page->fieldset()),
            'extra'             => [
                'is_home'    => $page->uri() === '/',
                'parent_url' => URL::parent($url)
            ],
            'suggestions' => $this->getSuggestions($page->fieldset()),
        ]);
    }

    /**
     * Build the redirect.
     *
     * @param  Request  $request
     * @param  \Statamic\Data\Pages\Page  $page
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirect(Request $request, $page)
    {
        $parameters = ['url' => ltrim($page->url(), '/')];

        if (! $request->continue) {
            return route('pages');
        }

        return route('page.edit', $parameters);
    }

    /**
     * Fetch the page from the given URL.
     *
     * @param  string  $url
     * @return \Statamic\Data\Pages\Page|null
     */
    private function page($url)
    {
        return Page::whereUri(URL::format($url));
    }

    /**
     * Return the locale from the request.
     *
     * @param  Request  $request
     * @return string
     */
    private function locale(Request $request)
    {
        return $request->query('locale', site_locale());
    }

    /**
     * Whether the user is authorized to publish the object.
     *
     * @param Request $request
     * @return bool
     */
    protected function canPublish(Request $request)
    {
        return $request->user()->can(
            $request->new ? 'pages:create' : 'pages:edit'
        );
    }
}
