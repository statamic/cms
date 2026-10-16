<?php

namespace Statamic\Forms\Fields;

use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Statamic\Fields\ConfigFields;
use Statamic\Fields\Field;
use Statamic\Rules\Handle;
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
        $reserved = [...Field::reservedHandles(), 'date', 'entry', 'message', 'messages'];

        $fields = collect([
            'display' => [
                'display' => __('Label'),
                'type' => 'text',
                'focus' => true,
                'validate' => 'required',
            ],
            'handle' => [
                'display' => __('Handle'),
                'instructions' => __('statamic::messages.form_fields_handle_instructions'),
                'type' => 'slug',
                'from' => 'display',
                'async' => false,
                'separator' => '_',
                'validate' => [
                    'required',
                    new Handle,
                    'not_in:'.implode(',', $reserved),
                ],
                'show_regenerate' => true,
                'if' => ['isNew' => 'equals true'],
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
