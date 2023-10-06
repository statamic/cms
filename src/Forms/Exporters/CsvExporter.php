<?php

namespace Statamic\Forms\Exporters;

use League\Csv\Writer;
use SplTempFileObject;

class CsvExporter extends AbstractExporter
{
    /**
     * @var Writer
     */
    private $writer;

    /**
     * Create a new CsvExporter.
     */
    public function __construct()
    {
        $this->writer = Writer::createFromFileObject(new SplTempFileObject);
        $this->writer->setDelimiter(config('statamic.forms.csv_delimiter', ','));
    }

    /**
     * Perform the export.
     *
     * @return string
     */
    public function export()
    {
        $this->insertHeaders();

        $this->insertData();

        return (string) $this->writer;
    }

    /**
     * Insert the headers into the CSV.
     */
    private function insertHeaders()
    {
        $key = config('statamic.forms.csv_headers', 'handle');

        $headers = $this->form()->fields()
            ->map(fn ($field) => $key === 'display' ? $field->display() : $field->handle())
            ->push($key === 'display' ? __('Date') : 'date')
            ->values()->all();

        $this->writer->insertOne($headers);
    }

    /**
     * Insert the submission data into the CSV.
     */
    private function insertData()
    {
        $data = $this->form()->submissions()->map(function ($submission) {
            $submission = $submission->toArray();

            $submission['date'] = (string) $submission['date'];

            unset($submission['id']);

            return collect($submission)->map(function ($value) {
                return (is_array($value)) ? implode(', ', $value) : $value;
            })->all();
        })->all();

        $this->writer->insertAll($data);
    }
}
