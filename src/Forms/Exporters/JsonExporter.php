<?php

namespace Statamic\Forms\Exporters;

use Illuminate\Support\Uri;
use Statamic\Facades\Scope;
use Statamic\Fields\Field;

class JsonExporter extends Exporter
{
    protected static string $title = 'JSON';

    public function export(string $path): void
    {
        $file = fopen($path, 'w');
        if (! $file) {
            return;
        }

        fwrite($file, '[');

        $first = true;
        $this->query()
            ->lazy(10)
            ->each(function ($submission) use ($file, &$first) {
                if (! $first) {
                    fwrite($file, ',');
                }

                fwrite($file, json_encode($submission));

                $first = false;
            });

        fwrite($file, ']');
        fclose($file);
    }

    public function contentType(): string
    {
        return 'application/json';
    }

    public function extension(): string
    {
        return 'json';
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
