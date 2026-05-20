<?php

namespace Statamic\Forms\Fieldtypes;

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
        return $this->config();
    }

    public function title(): string
    {
        return $this->wrappedFieldtype?->title() ?? parent::title();
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
}
