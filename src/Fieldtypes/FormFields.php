<?php

namespace Statamic\Fieldtypes;

use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Illuminate\Support\Collection;
use Statamic\Contracts\Forms\Form;
use Statamic\Facades\Form as Forms;
use Statamic\Fields\Fieldtype;
use Statamic\Forms\Fields\FormField;
use Statamic\Support\Str;

use function Statamic\trans as __;

class FormFields extends Fieldtype
{
    use HasSelectOptions;

    private const NON_VALUE_CATEGORIES = ['information', 'structure'];

    protected $selectable = false;

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Selection & Options'),
                'fields' => [
                    'form' => [
                        'display' => __('Form'),
                        'type' => 'text',
                        'width' => 50,
                    ],
                    'categories' => [
                        'display' => __('Categories'),
                        'type' => 'select',
                        'options' => $this->categoryOptions(),
                        'multiple' => true,
                        'width' => 50,
                    ],
                    'prefix' => [
                        'display' => __('Prefix'),
                        'type' => 'text',
                        'width' => 50,
                    ],
                    'taggable' => [
                        'display' => __('Allow additions'),
                        'type' => 'toggle',
                        'default' => false,
                        'width' => 50,
                    ],
                ],
            ],
            [
                'display' => __('Appearance'),
                'fields' => [
                    'placeholder' => [
                        'display' => __('Placeholder'),
                        'instructions' => __('statamic::fieldtypes.select.config.placeholder'),
                        'type' => 'text',
                        'width' => 50,
                    ],
                    'clearable' => [
                        'display' => __('Clearable'),
                        'instructions' => __('statamic::fieldtypes.select.config.clearable'),
                        'type' => 'toggle',
                        'default' => false,
                        'width' => 50,
                    ],
                    'searchable' => [
                        'display' => __('Searchable'),
                        'instructions' => __('statamic::fieldtypes.select.config.searchable'),
                        'type' => 'toggle',
                        'default' => true,
                        'width' => 50,
                    ],
                ],
            ],
            [
                'display' => __('Boundaries & Limits'),
                'fields' => [
                    'multiple' => [
                        'display' => __('Multiple'),
                        'instructions' => __('statamic::fieldtypes.select.config.multiple'),
                        'type' => 'toggle',
                        'default' => false,
                        'width' => 50,
                    ],
                    'max_items' => [
                        'display' => __('Max Items'),
                        'instructions' => __('statamic::messages.max_items_instructions'),
                        'type' => 'integer',
                        'min' => 1,
                        'width' => 50,
                        'if' => ['multiple' => true],
                    ],
                ],
            ],
        ];
    }

    private function categoryOptions(): array
    {
        return FormFieldtypeRepository::classes()
            ->unique()
            ->flatMap(fn (string $class) => app($class)->categories())
            ->push('other')
            ->unique()
            ->mapWithKeys(fn (string $category) => [$category => __(Str::title(Str::humanize($category)))])
            ->all();
    }

    protected function getOptions(): array
    {
        $prefix = $this->config('prefix');

        return $this->fields()
            ->map(fn (FormField $field) => [
                'value' => $prefix.$field->handle(),
                'label' => $field->display(),
                'icon' => $field->fieldtype()->icon(),
                'category' => $this->category($field),
            ])
            ->values()
            ->all();
    }

    private function fields(): Collection
    {
        if (! $form = $this->form()) {
            return collect();
        }

        $categories = $this->config('categories');

        return $form->formFields()->fields()->filter(function (FormField $field) use ($categories): bool {
            return $categories
                ? in_array($this->category($field), $categories)
                : ! in_array($this->category($field), self::NON_VALUE_CATEGORIES);
        });
    }

    private function category(FormField $field): string
    {
        return $field->fieldtype()->categories()[0] ?? 'other';
    }

    private function form(): ?Form
    {
        if ($handle = $this->config('form')) {
            return Forms::find($handle);
        }

        $parent = $this->field?->parent();

        return $parent instanceof Form ? $parent : null;
    }
}
