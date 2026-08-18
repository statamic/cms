<?php

namespace Statamic\Forms\Fieldtypes;

use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Statamic\Fields\Fieldtype;
use Statamic\Forms\Fields\FormFieldtype;

class Fallback extends FormFieldtype
{
    protected ?Fieldtype $wrappedFieldtype = null;

    public function wrapping(Fieldtype $fieldtype): static
    {
        $this->wrappedFieldtype = $fieldtype;

        return $this;
    }

    public function configFieldItems(): array
    {
        if (! $this->wrappedFieldtype) {
            return parent::configFieldItems();
        }

        return collect($this->wrappedFieldtype->configFields()->toPublishArray())
            ->keyBy('handle')
            ->all();
    }

    public function toFieldArray(): array
    {
        return $this->field?->config() ?? [];
    }

    public function title(): string
    {
        return $this->wrappedFieldtype?->title() ?? parent::title();
    }

    public function icon(): string
    {
        if (! $this->wrappedFieldtype) {
            return parent::icon();
        }

        if ($equivalent = $this->equivalentFormFieldtype()) {
            return $equivalent->icon();
        }

        return $this->wrappedFieldtype->icon();
    }

    public function toArray(): array
    {
        if (! $this->wrappedFieldtype) {
            return parent::toArray();
        }

        return [
            'handle' => $this->wrappedFieldtype->handle(),
            'title' => $this->wrappedFieldtype->title(),
            'categories' => [],
            'keywords' => $this->wrappedFieldtype->keywords(),
            'icon' => $this->wrappedFieldtype->icon(),
            'config' => $this->wrappedFieldtype->configFields()->toPublishArray(),
        ];
    }

    public function view(): string
    {
        if (! $this->wrappedFieldtype) {
            return parent::view();
        }

        $handle = $this->wrappedFieldtype->handle();
        $language = config('statamic.templates.language', 'antlers');

        $views = [
            "statamic::forms.fields.{$handle}",
            "statamic::forms.{$language}.fields.{$handle}",
        ];

        if ($equivalentFormFieldtype = $this->equivalentFormFieldtype()) {
            $views = [
                ...$views,
                "statamic::forms.fields.{$equivalentFormFieldtype->handle()}",
                "statamic::forms.{$language}.fields.{$equivalentFormFieldtype->handle()}",
            ];
        }

        return collect($views)->first(fn (string $view): bool => view()->exists($view))
            ?? $this->wrappedFieldtype->view();
    }

    private function equivalentFormFieldtype(): ?FormFieldtype
    {
        if (in_array($this->wrappedFieldtype->handle(), ['assets', 'files'])) {
            return FormFieldtypeRepository::find('upload');
        }

        $class = FormFieldtypeRepository::classes()
            ->filter(fn ($class) => $class::fieldtype() === $this->wrappedFieldtype->handle())
            ->sortBy(fn ($class) => app($class)->order() ?? PHP_INT_MAX)
            ->first();

        return $class ? app($class) : null;
    }
}
