<?php

namespace Statamic\Forms\Exporters;

use League\Csv\Writer;
use SplTempFileObject;
use Statamic\Support\Arr;

class CsvExporter extends Exporter
{
    private Writer $writer;
    protected static string $title = 'CSV';

    public function export(): string
    {
        $this->writer = Writer::createFromFileObject(new SplTempFileObject);
        $this->writer->setDelimiter(Arr::get($this->config, 'delimiter', config('statamic.forms.csv_delimiter', ',')));

        $this->insertHeaders();

        $this->insertData();

        return (string) $this->writer;
    }

    private function insertHeaders()
    {
        $key = Arr::get($this->config, 'headers', config('statamic.forms.csv_headers', 'handle'));

        $headers = $this->form->fields()
            ->map(fn ($field) => $key === 'display' ? $field->display() : $field->handle())
            ->push($key === 'display' ? __('Date') : 'date')
            ->values()->all();

        $this->writer->insertOne($headers);
    }

    private function insertData()
    {
        $data = $this->form->submissions()->map(function ($submission) {
            $submission = $submission->toArray();

            $submission['date'] = (string) $submission['date'];

            unset($submission['id']);

            return collect($submission)->map(function ($value) {
                return (is_array($value)) ? implode(', ', $value) : $value;
            })->all();
        })->all();

        $this->writer->insertAll($data);
    }

    public function extension(): string
    {
        return 'csv';
    }
}
