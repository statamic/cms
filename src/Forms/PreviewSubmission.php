<?php

namespace Statamic\Forms;

use Illuminate\Support\Str;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\Fields\Field;
use Statamic\Forms\Fields\FormField;

class PreviewSubmission
{
    public function values(FormContract $form): array
    {
        return $form->blueprint()->fields()->all()
            ->mapWithKeys(fn (Field $field) => [$field->handle() => $this->valueForField($field)])
            ->all();
    }

    private function valueForField(Field $field): mixed
    {
        return match ($field->type()) {
            'text' => $this->textValueFor($field),
            'textarea', 'markdown', 'hidden', 'revealer' => $this->placeholder($field),
            'slug' => Str::slug(__($field->display())),
            'list' => [$this->placeholder($field)],
            'email' => 'name@example.com',
            'link', 'url' => 'https://example.com',
            'integer', 'range', 'float', 'opinion_scale', 'star_rating' => $this->numberValueFor($field),
            'toggle' => true,
            'date' => now()->toDateString(),
            'time' => now()->format('H:i'),
            'date_and_time' => now()->toIso8601String(),
            'select', 'radio', 'button_group', 'yes_no', 'image_choice' => $field->get('multiple')
                ? array_slice(FieldOptions::keys($field), 0, 1)
                : $this->firstOptionKey($field),
            'checkboxes' => array_slice(FieldOptions::keys($field), 0, 1),
            'ranking' => FieldOptions::keys($field),
            'dictionary' => $this->dictionaryValueFor($field),
            'group' => $this->groupValueFor($field),
            default => null,
        };
    }

    private function placeholder(Field $field): string
    {
        return '['.__($field->display()).']';
    }

    private function textValueFor(Field $field): string
    {
        return match ($field->get('input_type')) {
            'email' => 'name@example.com',
            'url' => 'https://example.com',
            default => $this->placeholder($field),
        };
    }

    private function numberValueFor(Field $field): int|float
    {
        $min = $field->get('min');

        return is_numeric($min) ? $min + 0 : 1;
    }

    private function firstOptionKey(Field $field): mixed
    {
        return FieldOptions::keys($field)[0] ?? null;
    }

    private function dictionaryValueFor(Field $field): mixed
    {
        try {
            $key = array_key_first($field->fieldtype()->dictionary()->options());
        } catch (\Throwable) {
            return null;
        }

        if ($key === null) {
            return null;
        }

        return $field->get('max_items') === 1 ? $key : [$key];
    }

    private function groupValueFor(Field $field): array
    {
        return collect($field->get('fields', []))
            ->filter(fn ($config) => is_array($config) && isset($config['handle'], $config['field']))
            ->mapWithKeys(function (array $config) {
                $nested = new Field($config['handle'], (new FormField($config['handle'], $config['field']))->toFieldArray());

                return [$nested->handle() => $this->valueForField($nested)];
            })
            ->all();
    }
}
