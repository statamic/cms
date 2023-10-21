<?php

namespace Statamic\Forms\Exporters;

use League\Csv\Writer;
use SplTempFileObject;
use Statamic\Support\Arr;

class CsvExporter extends AbstractExporter
{
    /**
     * @var Writer
     */
    private $writer;

    protected static $title = 'CSV';

    /**
     * Perform the export.
     *
     * @return string
     */
    public function export()
    {
        $this->writer = Writer::createFromFileObject(new SplTempFileObject);
        $this->writer->setDelimiter(Arr::get($this->config, 'csv_delimiter', config('statamic.forms.csv_delimiter', ',')));

        $this->insertHeaders();

        $this->insertData();

        return (string) $this->writer;
    }

    /**
     * Insert the headers into the CSV.
     */
    private function insertHeaders()
    {
        $key = Arr::get($this->config, 'csv_headers', config('statamic.forms.csv_headers', 'handle'));

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

    /**
     * Get the extension.
     *
     * @return string
     */
    public function extension()
    {
        return 'csv';
    }
}
