<?php

namespace Statamic\Forms\Exporters;

use Statamic\Support\Arr;

class JsonExporter extends Exporter
{
    protected static string $title = 'JSON';

    public function export(): string
    {
        $columns = $this->columns()->push('id')->all();

        $submissions = $this->submissions()
            ->map(fn ($submission) => Arr::only($submission->toArray(), $columns))
            ->all();

        return json_encode($submissions);
    }

    public function supportsColumnSelection(): bool
    {
        return true;
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
