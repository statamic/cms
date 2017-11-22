<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Page;
use Statamic\API\YAML;
use Statamic\API\File;
use Statamic\API\Config;
use Statamic\API\Folder;
use Statamic\API\Helper;
use Statamic\API\Stache;
use Statamic\API\Content;
use Statamic\API\Collection;
use Statamic\Contracts\Data\Pages\PageTreeReorderer;

/**
 * Controller for the page tree
 */
class PagesController extends CpController
{
    /**
     * View for /cp/pages
     */
    public function pages()
    {
        $this->access('pages:edit');

        $this->ensureHome();

        $home = Page::whereUri('/');

        $home_data = [
            'title' => array_get($home->data(), 'title', t('home')),
            'id' => array_get($home->data(), 'id')
        ];

        if ($home && $home->hasEntries()) {
            $collection = Collection::whereHandle($home->entriesCollection());
            $home_data['has_entries'] = true;
            $home_data['create_entry_url'] = route('entry.create', ['url' => $home->entriesCollection()]);
            $home_data['entries_url'] = route('entries.show', ['collection' => $home->entriesCollection()]);
        }

        $data = [
            'title' => trans('cp.nav_pages'),
            'home' => $home_data
        ];

        return view('pages', $data);
    }

    /**
     * Get the page tree as JSON
     *
     * @return array
     */
    public function get()
    {
        $this->access('pages:edit');

        $tree = Content::tree('/', INF, false, request('drafts', true), null, request('locale'));

        if ($tree) {
            $data = $this->transformTree($tree);
            $data = $this->sortTreeData($data);
        } else {
            $data = [];
        }


        return [
            'pages' => $data
        ];
    }


    /**
     * Transform a content tree into a format suitable for page-tree
     *
     * @param array $tree
     * @return array
     */
    private function transformTree($tree)
    {
        foreach ($tree as $item) {
            /** @var \Statamic\Contracts\Data\Pages\Page $page */
            $page = $item['page'];

            $uri = $page->uri();
            $url = $page->url();

            $editUrl = $page->in(default_locale())->editUrl();
            $locale = request('locale');
            if ($locale !== default_locale()) {
                $editUrl .= '?locale=' . $locale;
            }

            $data[] = [
                'id'          => $page->id(),
                'order'       => $page->order(),
                'title'       => (string) $page->getWithDefaultLocale('title'),
                'url'         => $url,
                'uri'         => $uri,
                'extension'   => $page->dataType(),
                'edit_url'    => $editUrl,
                'create_child_url' => route('page.create', ['url' => ltrim($uri, '/')]),
                'slug'        => $page->slug(),
                'published'   => $page->published(),
                'has_entries' => $page->hasEntries(),
                'create_entry_url' => route('entry.create', ['url' => $page->entriesCollection()]),
                'entries_url' => route('entries.show', ['collection' => $page->entriesCollection()]),
                'collapsed'   => false,
                'items'       => (! empty($item['children'])) ? $this->transformTree($item['children']) : []
            ];
        }

        return $data;
    }

    /**
     * Sort the transformed tree's data
     *
     * @param array $data
     * @return array
     */
    private function sortTreeData($data)
    {
        $keys   = [];
        $titles = [];

        foreach ($data as $key => $item) {
            // Order key array. No order key results in a crazy high order number.
            // Hopefully no one has that many pages...
            $keys[]   = (is_null($item['order'])) ? 100000 : $item['order'];

            // After pages are sorted by order key, the remainers get sorted
            // alphabetically. We'll go by title, and falling back to slug.
            $titles[] = array_get($item, 'title', $item['slug']);

            // Recursion for children
            if (! empty($item['items'])) {
                $data[$key]['items'] = $this->sortTreeData($item['items']);
            }
        }

        array_multisort($keys, SORT_ASC, SORT_NUMERIC, $titles, SORT_ASC, $data);

        return $data;
    }

    /**
     * Page tree save submission
     *
     * @param PageTreeReorderer $reorderer
     * @return array
     */
    public function save(PageTreeReorderer $reorderer)
    {
        $this->authorize('pages:reorder');

        // Grab the JSON payload
        $tree = $this->request->input('pages');

        $reorderer->reorder($tree);

        Stache::clear();

        return [
            'success' => true,
            'message' => 'Pages updated successfully.'
        ];
    }

    /**
     * Delete a page
     *
     * @return array
     */
    public function delete()
    {
        $this->authorize('pages:delete');

        $uuid = $this->request->input('uuid');

        if (! $content = Page::find($uuid)) {
            return [
                'success' => false,
                'message' => 'Page does not exist'
            ];
        }

        $content->delete();

        return [
            'success' => true
        ];
    }

    public function mountCollection()
    {
        $this->authorize('super');

        $page = Page::find($this->request->input('id'));

        if ($this->request->has('collection')) {
            $page->set('mount', $this->request->input('collection'));
        } else {
            $page->remove('mount');
        }

        $page->save();

        $this->success(t('page_updated'));

        return [
            'success' => true
        ];
    }

    /**
     * Create the home page if it doesn't already exist
     */
    private function ensureHome()
    {
        $files = collect_files(Folder::disk('content')->getFiles('pages'))->removeHidden();

        if ($files->isEmpty()) {
            $path = 'pages/index.' . Config::get('system.default_extension');
            $yaml = YAML::dump(['title' => 'Home', 'id' => Helper::makeUuid()]);
            File::disk('content')->put($path, $yaml);
        }
    }
}
