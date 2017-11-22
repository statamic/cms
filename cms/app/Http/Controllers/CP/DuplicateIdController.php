<?php

namespace Statamic\Http\Controllers;

use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\API\Helper;
use Statamic\API\Stache;
use Illuminate\Http\Request;
use Statamic\Stache\Stache as TheStache;

class DuplicateIdController extends CpController
{
    public function index(TheStache $stache)
    {
        return view('resolve-duplicate-ids', [
            'title' => t('duplicate_id_title'),
            'duplicates' => $stache->duplicates()
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('resolve_duplicates');

        $path = $request->path;

        if (! File::disk('content')->exists($path)) {
            return back()->withErrors('Path does not exist.');
        }

        $this->generateNewId($path);

        Stache::clear();

        return back()->with('success', 'File updated with new ID.');
    }

    private function generateNewId($path)
    {
        $yaml = YAML::parse(File::disk('content')->get($path));

        $yaml['id'] = Helper::makeUuid();

        $content = array_pull($yaml, 'content');
        $contents = YAML::dump($yaml, $content);

        File::disk('content')->put($path, $contents);
    }
}
