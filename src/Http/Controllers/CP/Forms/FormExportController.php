<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Http\Controllers\CP\CpController;

class FormExportController extends CpController
{
    public function export($form, $type)
    {
        $this->authorize('view', $form);

        if (! $exporter = $form->exporter($type)) {
            throw new NotFoundHttpException;
        }

        return $this->request->has('download') ? $exporter->download() : $exporter->response();
    }
}
