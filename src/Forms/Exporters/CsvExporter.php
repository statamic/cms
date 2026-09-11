<?php

namespace Statamic\Forms\Exporters;

use League\Csv\EscapeFormula;
use League\Csv\Writer;
use SplTempFileObject;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class CsvExporter extends Exporter
{
    private Writer $writer;
    protected static string $title = 'CSV';

    public function export(): string
    {
        $this->writer = Writer::createFromFileObject(new SplTempFileObject);
        $this->writer->setDelimiter(Arr::get($this->config, 'delimiter', config('statamic.forms.csv_delimiter', ',')));
        $this->writer->addFormatter(new EscapeFormula("'"));

        $this->insertHeaders();

        $this->insertData();

        return (string) $this->writer;
    }

    private function insertHeaders()
    {
        $key = Arr::get($this->config, 'headers', config('statamic.forms.csv_headers', 'handle'));

        $headers = $this->columns()->map(function ($handle) use ($key) {
            if ($key !== 'display') {
                return $handle;
            }

            return $handle === 'date' ? __('Date') : $this->form->fields()->get($handle)->display();
        })->all();

        $this->writer->insertOne($headers);
    }

    private function insertData()
    {
        $columns = $this->columns();

        $data = $this->submissions()->map(function ($submission) use ($columns) {
            $values = $submission->toArray();

            $values['date'] = (string) $values['date'];

            return $columns->map(function ($column) use ($values) {
                $value = $values[$column] ?? null;

                return is_array($value) ? implode(', ', $value) : $value;
            })->all();
        })->all();

        $this->writer->insertAll($data);
    }

    public function supportsColumnSelection(): bool
    {
        return true;
    }

    public function extension(): string
    {
        return 'csv';
    }
}
