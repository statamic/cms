<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Events\DuplicateIdRegenerated;
use Statamic\Facades\File;
use Statamic\Facades\Stache;
use Statamic\Support\Str;

class DuplicatesController extends CpController
{
    public function index()
    {
        $this->authorize('resolve duplicate ids');

        return view('statamic::duplicates', [
            'duplicates' => $this->getDuplicates(),
        ]);
    }

    private function getDuplicates()
    {
        return Stache::duplicates()->find()->all()->flatMap(function ($duplicates) {
            return $duplicates;
        })->map(function ($paths) {
            return collect($paths)->map(function ($path) {
                return Str::after($path, base_path().'/');
            })->all();
        });
    }

    public function regenerate(Request $request)
    {
        $this->authorize('resolve duplicate ids');

        $request->validate(['path' => 'required']);

        $path = base_path().'/'.$request->path;

        $store = $this->getStoreByPath($path);

        $item = $store->makeItemFromFile($path, File::get($path));

        $item->id(Stache::generateId());

        $item->writeFile();

        Stache::clear();

        DuplicateIdRegenerated::dispatch();

        return back()->with('success', __('ID regenerated and Stache cleared'));
    }

    private function getStoreByPath(string $path)
    {
        foreach (Stache::duplicates()->all() as $store => $duplicates) {
            foreach ($duplicates as $id => $paths) {
                foreach ($paths as $dupePath) {
                    if ($dupePath === $path) {
                        return Stache::store($store);
                    }
                }
            }
        }
    }
}
