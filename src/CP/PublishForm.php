<?php

namespace Statamic\CP;

use Illuminate\Contracts\Support\Responsable;
use Statamic\Fields\Blueprint;

class PublishForm implements Responsable
{
    private array $values = [];
    private string $submitUrl;
    private string $submitMethod = 'PATCH';

    public function __construct(private $blueprint)
    {
    }

    public static function make(Blueprint $blueprint): self
    {
        return new self($blueprint);
    }

    public function values(array $values): self
    {
        $this->values = $values;

        return $this;
    }

    public function submittingTo(string $url, string $method = 'PATCH'): self
    {
        $this->submitUrl = $url;
        $this->submitMethod = $method;

        return $this;
    }

    public function submit(array $values): array
    {
        $fields = $this->blueprint->fields()->addValues($values);

        $fields->validate();

        return $fields->process()->values()->all();
    }

    public function toResponse($request)
    {
        $fields = $this
            ->blueprint
            ->fields()
            ->addValues($this->values)
            ->preProcess();

        return view('statamic::publish.form', [
            'blueprint' => $this->blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'submitUrl' => $this->submitUrl,
            'submitMethod' => $this->submitMethod,
        ]);
    }
}
