<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Statamic\Exceptions\FatalException;
use Statamic\Facades\File;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;

class FormExportController extends CpController
{
    public function export($form, $type)
    {
        $this->authorize('view', $form);

        $exporters = config('statamic.forms.exporters', []);

        if (! $exporterConfig = Arr::get($exporters, $type)) {
            throw new FatalException("Exporter of type [$type] is not defined.");
        }

        if (($limits = $exporterConfig['forms'] ?? []) && ! in_array($form->handle(), $limits)) {
            throw new FatalException("Exporter of type [$type] is not allowed to be used on {$form->handle()}.");
        }

        if (! (isset($exporterConfig['class']) && class_exists($exporterConfig['class']))) {
            throw new FatalException("Exporter of type [$type] does not exist.");
        }

        $exporter = new $exporterConfig['class'];
        $exporter->config(Arr::except($exporterConfig, ['class', 'forms']));
        $exporter->form($form);

        $content = $exporter->export();

        if ($this->request->has('download')) {
            $path = storage_path('statamic/tmp/forms/'.$form->handle().'-'.time().'.'.$type);
            File::put($path, $content);
            $response = response()->download($path)->deleteFileAfterSend(true);
        } else {
            $response = response($content)->header('Content-Type', $exporter->contentType());
        }

        return $response;
    }
}
