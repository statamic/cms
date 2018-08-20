<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Structure;
use Illuminate\Http\Request;
use Statamic\Exceptions\AuthorizationException;

class StructuresController extends CpController
{
    public function index()
    {
        $all = Structure::all();

        $structures = $all->filter(function ($structure) {
            return request()->user()->can('view', $structure);
        });

        if ($structures->isEmpty() && $all->count() != $structures->count()) {
            throw new AuthorizationException('You are not authorized to view any structures.');
        }

        return view('statamic::structures.index', compact('structures'));
    }

    public function edit($structure)
    {
        return view('statamic::structures.edit', [
            'structure' => Structure::find($structure)
        ]);
    }

    public function get($structure)
    {
        $tree = (new \Statamic\Addons\Nav\ContentTreeBuilder)->build([
            'structure' => $structure,
            'include_home' => false
        ]);

        $data = $this->transformTree($tree);
        $data = $this->sortTreeData($data);

        return ['pages' => $data];
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
                'has_entries' => false, // TODO $page->hasEntries(),
                'create_entry_url' => null, // route('entry.create', ['url' => $page->entriesCollection()]),
                'entries_url' => null, // route('entries.show', ['collection' => $page->entriesCollection()]),
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

    public function update(Request $request, $structure)
    {
        $structure = Structure::find($structure);

        $structure->data(array_merge($structure->data(), [
            'tree' => $this->toTree($request->pages)
        ]))->save();
    }

    protected function toTree($items)
    {
        return collect($items)->map(function ($item) {
            return [
                'entry' => $item['id'],
                'children' => $this->toTree($item['items'])
            ];
        })->all();
    }
}
