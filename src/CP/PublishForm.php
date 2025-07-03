<?php

namespace Statamic\CP;

use Illuminate\Contracts\Support\Responsable;
use Statamic\Fields\Blueprint;

class PublishForm implements Responsable
{
    private ?string $icon = null;
    private string $title = '';
    private array $values = [];
    private $parent = null;
    private bool $readOnly = false;
    private string $submitUrl;
    private string $submitMethod = 'PATCH';
    private bool $usingConfigLayout = false;

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

    public function readOnly(bool $readOnly = true): self
    {
        $this->readOnly = $readOnly;

        return $this;
    }

    public function submittingTo(string $url, string $method = 'PATCH'): self
    {
        $this->submitUrl = $url;
        $this->submitMethod = $method;

        return $this;
    }

    public function usingConfigLayout()
    {
        $this->usingConfigLayout = true;

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

        $viewData = [
            'blueprint' => $this->blueprint->toPublishArray(),
            'icon' => $this->icon,
            'title' => $this->title,
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'readOnly' => $this->readOnly,
            'submitUrl' => $this->submitUrl,
            'submitMethod' => $this->submitMethod,
            'usingConfigLayout' => $this->usingConfigLayout,
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic::publish.form', $viewData);
    }
}
