<?php

namespace Statamic\Fields;

use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Statamic\Forms\Fields as FormFields;

class FieldtypeRepository
{
    private $fieldtypes = [];

    private array $formFieldtypeMappings = [
        'text' => [FormFields\Email::class, FormFields\ShortAnswer::class],
        'textarea' => [FormFields\LongAnswer::class],
    ];

    public function preloadable()
    {
        return $this->classes()->filter(function ($class) {
            return $class::preloadable();
        });
    }

    public function find($handle)
    {
        if (isset($this->fieldtypes[$handle])) {
            return clone $this->fieldtypes[$handle];
        }

        if (! ($fieldtypes = $this->classes())->has($handle)) {
            throw new \Statamic\Exceptions\FieldtypeNotFoundException($handle);
        }

        return $this->fieldtypes[$handle] = app($fieldtypes->get($handle));
    }

    public function classes()
    {
        return app('statamic.fieldtypes');
    }

    public function handles()
    {
        return $this->classes()->map(function ($class) {
            return $class::handle();
        });
    }

    /**
     * @deprecated Use FormFieldtypeRepository::makeSelectable() instead.
     */
    public function makeSelectableInForms($handle)
    {
        $mappings = $this->formFieldtypeMappings[$handle] ?? [];

        foreach ($mappings as $mapping) {
            FormFieldtypeRepository::makeSelectable($mapping::handle());
        }
    }

    /**
     * @deprecated Use FormFieldtypeRepository::makeUnselectable() instead.
     */
    public function makeUnselectableInForms($handle)
    {
        $mappings = $this->formFieldtypeMappings[$handle] ?? [];

        foreach ($mappings as $mapping) {
            FormFieldtypeRepository::makeUnselectable($mapping::handle());
        }
    }

    /**
     * @deprecated Use FormFieldtypeRepository::hasBeenMadeSelectable() instead.
     */
    public function hasBeenMadeSelectableInForms($handle)
    {
        $mappings = $this->formFieldtypeMappings[$handle] ?? [];

        if (empty($mappings)) {
            return false;
        }

        return collect($mappings)->every(function (string $formFieldtype): bool {
            return FormFieldtypeRepository::hasBeenMadeSelectable($formFieldtype::handle());
        });
    }

    /**
     * @deprecated Use FormFieldtypeRepository::selectableIsOverriden() instead.
     */
    public function selectableInFormIsOverriden($handle)
    {
        $mappings = $this->formFieldtypeMappings[$handle] ?? [];

        if (empty($mappings)) {
            return false;
        }

        return collect($mappings)->every(function (string $formFieldtype): bool {
            return FormFieldtypeRepository::selectableIsOverriden($formFieldtype::handle());
        });
    }
}
