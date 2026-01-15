<?php

namespace Statamic\Forms\Exporters;

use Illuminate\Support\Uri;
use League\Csv\Writer;
use Statamic\Facades\Scope;
use Statamic\Fields\Field;
use Statamic\Support\Arr;

class CsvExporter extends Exporter
{
    private Writer $writer;
    protected static string $title = 'CSV';

    public function export(string $path): void
    {
        $this->writer = Writer::createFromPath($path);
        $this->writer->setDelimiter(Arr::get($this->config, 'delimiter', config('statamic.forms.csv_delimiter', ',')));

        $this->insertHeaders();
        $this->query()
            ->lazy(10)
            ->each(fn ($submission) => $this->writer->insertOne(
                collect($submission->toArray())
                    ->except('id')
                    ->map(fn ($value) => (is_array($value)) ? implode(', ', $value) : ((string) $value))
                    ->all()
            ));
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

    public function extension(): string
    {
        return 'csv';
    }

    private function query()
    {
        $form = $this->form;
        $query = $form->querySubmissions();

        if ($url = request()->headers->get('referer')) {
            $url = Uri::of($url);

            if ($search = $url->query()->get('search')) {
                $query->where('date', 'like', '%'.$search.'%');

                $form->blueprint()
                    ->fields()
                    ->all()
                    ->filter(fn (Field $field) => in_array($field->type(), ['text', 'textarea', 'integer']))
                    ->each(fn (Field $field) => $query->orWhere($field->handle(), 'like', '%'.$search.'%'));
            }

            if ($filters = $url->query()->get('filters')) {
                $filters = base64_decode($filters);
                $filters = json_validate($filters) ? json_decode($filters, true) : [];
                collect($filters)
                    ->map(fn ($values, $handle) => (object) [
                        'filterInstance' => Scope::find($handle, ['form' => $form->handle()]),
                        'values' => $values,
                    ])
                    ->filter(fn ($filter) => $filter->filterInstance !== null)
                    ->each(fn ($filter) => $filter->filterInstance->apply($query, $filter->values));
            }

            $sortField = $url->query()->get('sort', 'date');
            $sortDirection = $url->query()->get('order', $sortField === 'date' ? 'desc' : 'asc');
            $query->orderBy($sortField, $sortDirection);
        }

        return $query;
    }
}
