<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Cache;
use Statamic\Importing\Statamic\StatamicImporter;

class ImportController extends CpController
{
    public function index()
    {
        return view('import.upload', ['title' => 'Importer',]);
    }

    public function upload()
    {
        $this->access('importer');

        $stream = fopen($this->request->file('file'), 'r+');
        $contents = stream_get_contents($stream);
        fclose($stream);

        try {
            $prepared = $this->importer()->prepare($contents);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }

        Cache::put('importer.statamic.prepared', $prepared);

        return redirect()->route('import.configure');
    }

    public function configure()
    {
        $this->access('importer');

        if (! $data = Cache::get('importer.statamic.prepared')) {
            return redirect()->route('import');
        }

        return view('import.import', [
            'summary' => $this->importer()->summary($data)
        ]);
    }

    public function import()
    {
        $this->access('importer');

        $prepared = Cache::get('importer.statamic.prepared');

        $summary = $this->request->input('summary');

        $this->importer()->import($prepared, $summary);

        return ['success' => true];
    }

    private function importer()
    {
        return new StatamicImporter;
    }
}
