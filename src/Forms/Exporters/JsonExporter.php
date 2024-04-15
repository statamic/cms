<?php

namespace Statamic\Forms\Exporters;

class JsonExporter extends Exporter
{
    protected static string $title = 'JSON';

    public function export(): string
    {
        $submissions = $this->form->submissions()->toArray();

        return json_encode($submissions);
    }

    public function contentType(): string
    {
        return 'application/json';
    }

    public function extension(): string
    {
        return 'json';
    }
}
