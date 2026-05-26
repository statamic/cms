<?php

namespace Statamic\Forms\Fields;

use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Statamic\Exceptions\FormFieldtypeNotFoundException;
use Statamic\Fields\Field;
use Statamic\Forms\Fieldtypes\Fallback;
use Statamic\Fields\ConfigFields;
use Statamic\Support\Arr;
use Statamic\Support\Str;

use function Statamic\trans as __;

class FormField
{
    public function __construct(protected string $handle, protected array $config)
    {
    }

    public function handle(): string
    {
        return $this->handle;
    }

    public function config(): array
    {
        return $this->config;
    }

    public function type(): string
    {
        return Arr::get($this->config, 'type', 'short_answer');
    }

    public function fieldtype()
    {
        return FormFieldtypeRepository::find($this->type())->setField($this);
    }

    public function display()
    {
        return Arr::get($this->config, 'display', __(Str::slugToTitle($this->handle)));
    }

    public function instructions()
    {
        return Arr::get($this->config, 'instructions');
    }

    public function toField(): Field
    {
        return $this->fieldtype()->toField();
    }

    public function toFieldArray(): array
    {
        return $this->fieldtype()->toFieldArray();
    }

    public static function commonFieldOptions(): ConfigFields
    {
        $fields = collect([
            'display' => [
                'display' => __('Label'),
                'type' => 'text',
                'focus' => true,
            ],
            'instructions' => [
                'display' => __('Help Text'),
                'instructions' => __('statamic::messages.form_fields_instructions_instructions'),
                'type' => 'textarea',
            ],
        ])->map(fn ($field, $handle) => compact('handle', 'field'))->values()->all();

        return new ConfigFields($fields);
    }
}
