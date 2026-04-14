<?php

namespace Statamic\Forms\Fields;

use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Statamic\Exceptions\FormFieldtypeNotFoundException;
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
        try {
            return FormFieldtypeRepository::find($this->type())->setField($this);
        } catch (FormFieldtypeNotFoundException $e) {
            return (new Fallback)->setField($this);
        }
    }

    public function display()
    {
        return Arr::get($this->config, 'display', __(Str::slugToTitle($this->handle)));
    }

    public function instructions()
    {
        return Arr::get($this->config, 'instructions');
    }

    public function toFieldArray(): array
    {
        return $this->fieldtype()->toFieldArray();
    }

    // TODO: commonFieldOptions()
}
