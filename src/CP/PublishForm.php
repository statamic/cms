<?php

namespace Statamic\CP;

use Illuminate\Contracts\Support\Responsable;
use Statamic\Fields\Blueprint;

class PublishForm implements Responsable
{
    public string $icon = '';
    public string $title = '';
    private array $values = [];
    private $parent = null;
    private string $submitUrl;
    private string $submitMethod = 'PATCH';

    public function __construct(private $blueprint)
    {
    }

    public static function make(Blueprint $blueprint): self
    {
        return new self($blueprint);
    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function values(array $values): self
    {
        $this->values = $values;

        return $this;
    }

    public function parent($parent): self
    {
        $this->parent = $parent;

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
        $fields = $this
            ->blueprint
            ->fields()
            ->setParent($this->parent ?? null)
            ->addValues($values);

        $fields->validate();

        return $fields->process()->values()->all();
    }

    public function toResponse($request)
    {
        $fields = $this
            ->blueprint
            ->fields()
            ->setParent($this->parent ?? null)
            ->addValues($this->values)
            ->preProcess();

        return view('statamic::publish.form', [
            'blueprint' => $this->blueprint->toPublishArray(),
            'icon' => $this->icon,
            'title' => $this->title,
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'submitUrl' => $this->submitUrl,
            'submitMethod' => $this->submitMethod,
        ]);
    }
}
