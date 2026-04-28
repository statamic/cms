<?php

namespace Statamic\Forms\Fields;

use Statamic\Fields\Fieldtype;

class Fallback extends FormFieldtype
{
    protected ?Fieldtype $wrappedFieldtype = null;

    public function wrapping(Fieldtype $fieldtype): static
    {
        $this->wrappedFieldtype = $fieldtype;

        return $this;
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
            'categories' => $this->wrappedFieldtype->categories(),
            'keywords' => $this->wrappedFieldtype->keywords(),
            'icon' => $this->wrappedFieldtype->icon(),
            'config' => $this->wrappedFieldtype->configFields()->toPublishArray(),
        ];
    }
}
