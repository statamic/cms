<?php

namespace Statamic\Fields;

use Facades\Statamic\Fields\FieldtypeRepository;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Lang;
use Rebing\GraphQL\Support\Field as GqlField;
use Statamic\Contracts\Forms\Form;
use Statamic\Facades\GraphQL;
use Statamic\Rules\Handle;
use Statamic\Support\Arr;
use Statamic\Support\Str;

use function Statamic\trans as __;

class Field implements Arrayable
{
    protected $handle;
    protected $prefix;
    protected $config;
    protected $value;
    protected $parent;
    protected $parentField;
    protected $parentIndex;
    protected $validationContext;
    protected ?Form $form = null;

    public function __construct($handle, array $config)
    {
        $this->handle = $handle;
        $this->config = $config;
    }

    public function newInstance()
    {
        return (new static($this->handle, $this->config))
            ->setParent($this->parent)
            ->setParentField($this->parentField, $this->parentIndex)
            ->setValue($this->value);
    }

    public function setHandle(string $handle)
    {
        $this->handle = $handle;

        return $this;
    }

    public function handle()
    {
        return $this->handle;
    }

    public function handlePath()
    {
        $path = $this->parentField ? $this->parentField->handlePath() : [];

        $path[] = $this->handle();

        return $path;
    }

    public function fieldPathKeys()
    {
        $path = $this->parentField ? $this->parentField->fieldPathKeys() : [];

        if (isset($this->parentIndex)) {
            $path[] = $this->parentIndex;
        }

        $path[] = $this->handle();

        return $path;
    }

    public function fieldPathPrefix()
    {
        return implode('.', $this->fieldPathKeys());
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function prefix()
    {
        return $this->prefix;
    }

    public function parentIndex()
    {
        return $this->parentIndex;
    }

    public function type()
    {
        return Arr::get($this->config, 'type', 'text');
    }

    public function fieldtype()
    {
        return FieldtypeRepository::find($this->type())->setField($this);
    }

    public function display()
    {
        return Arr::get($this->config, 'display', __(Str::slugToTitle($this->handle)));
    }

    public function instructions()
    {
        return Arr::get($this->config, 'instructions');
    }

    public function visibility()
    {
        $visibility = Arr::get($this->config, 'visibility');

        $legacyReadOnly = Arr::get($this->config, 'read_only');

        if ($legacyReadOnly && ! $visibility) {
            return 'read_only';
        }

        return $visibility ?? 'visible';
    }

    public function alwaysSave()
    {
        return Arr::get($this->config, 'always_save', false);
    }

    public function rules()
    {
        $rules = [$this->handle => $this->addNullableRule(array_merge(
            $this->get('required') ? ['required'] : [],
            Validator::explodeRules($this->fieldtype()->fieldRules()),
            Validator::explodeRules($this->fieldtype()->rules())
        ))];

        $extra = collect($this->fieldtype()->extraRules())->map(function ($rules) {
            return $this->addNullableRule(Validator::explodeRules($rules));
        })->all();

        return array_merge($rules, $extra);
    }

    protected function addNullableRule($rules)
    {
        if (in_array('nullable', $rules)) {
            return $rules;
        }

        $nullable = true;

        foreach ($rules as $rule) {
            if (is_string($rule) && preg_match('/^required_?/', $rule)) {
                $nullable = false;
                break;
            }
        }

        if ($nullable) {
            $rules[] = 'nullable';
        }

        return $rules;
    }

    public function isRequired()
    {
        return collect($this->rules()[$this->handle])->contains('required');
    }

    public function setValidationContext($context)
    {
        $this->validationContext = $context;

        return $this;
    }

    public function validationContext($key = null)
    {
        return func_num_args() === 0 ? $this->validationContext : Arr::get($this->validationContext, $key);
    }

    public function validationAttributes()
    {
        $display = Lang::has($key = 'validation.attributes.'.$this->handle())
            ? Lang::get($key)
            : __($this->display());

        return array_merge(
            [$this->handle() => $display],
            $this->fieldtype()->extraValidationAttributes()
        );
    }

    public function isLocalizable()
    {
        return (bool) $this->get('localizable');
    }

    public function isListable()
    {
        if (is_null($this->get('listable'))) {
            return true;
        }

        if ($this->type() === 'section') {
            return false;
        }

        return (bool) $this->get('listable');
    }

    public function isVisibleOnListing()
    {
        if (is_null($this->get('listable'))) {
            return in_array($this->handle, ['title', 'slug', 'date', 'author']);
        }

        return ! in_array($this->get('listable'), [false, 'hidden'], true);
    }

    public function isSortable()
    {
        if ($this->get('visibility') === 'computed') {
            return false;
        }

        if (is_null($this->get('sortable'))) {
            return true;
        }

        return (bool) $this->get('sortable');
    }

    public function isFilterable()
    {
        if (is_null($this->get('filterable'))) {
            return $this->isListable();
        }

        return (bool) $this->get('filterable');
    }

    public function shouldBeDuplicated()
    {
        if (is_null($this->get('duplicate'))) {
            return true;
        }

        return (bool) $this->get('duplicate');
    }

    public function toPublishArray()
    {
        return array_merge($this->preProcessedConfig(), [
            'handle' => $this->handle,
            'prefix' => $this->prefix,
            'type' => $this->type(),
            'display' => $this->display(),
            'instructions' => $this->instructions(),
            'required' => $this->isRequired(),
            'visibility' => $this->visibility(),
            'read_only' => $this->visibility() === 'read_only', // Deprecated: Addon fieldtypes should now reference new `visibility` state.
            'always_save' => $this->alwaysSave(),
        ]);
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function value()
    {
        return $this->value;
    }

    public function defaultValue()
    {
        return $this->config['default'] ?? $this->fieldtype()->defaultValue();
    }

    public function validationValue()
    {
        return $this->fieldtype()->validationValue($this->value);
    }

    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    public function parent()
    {
        return $this->parent;
    }

    public function setParentField($field, $index = null)
    {
        $this->parentField = $field;
        $this->parentIndex = $index;

        return $this;
    }

    public function parentField()
    {
        return $this->parentField;
    }

    public function process()
    {
        return $this->newInstance()->setValue(
            $this->fieldtype()->process($this->value)
        );
    }

    public function preProcess()
    {
        $value = $this->value ?? $this->defaultValue();

        $value = $this->fieldtype()->preProcess($value);

        return $this->newInstance()->setValue($value);
    }

    public function preProcessIndex()
    {
        return $this->newInstance()->setValue(
            $this->fieldtype()->preProcessIndex($this->value)
        );
    }

    public function preProcessValidatable()
    {
        return $this->newInstance()->setValue(
            $this->fieldtype()->preProcessValidatable($this->value)
        );
    }

    public function augment()
    {
        return $this->newInstance()->setValue(
            new Value($this->value, $this->handle, $this->fieldtype(), $this->parent)
        );
    }

    public function shallowAugment()
    {
        return $this->newInstance()->setValue(
            (new Value($this->value, $this->handle, $this->fieldtype(), $this->parent))->shallow()
        );
    }

    public function toArray()
    {
        return array_merge($this->config, [
            'handle' => $this->handle,
            'width' => $this->config['width'] ?? 100,
        ]);
    }

    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    public function config(): array
    {
        return $this->config;
    }

    public function conditions(): array
    {
        return collect($this->config)->only([
            'if',
            'if_any',
            'show_when',
            'show_when_any',
            'unless',
            'unless_any',
            'hide_when',
            'hide_when_any',
        ])->all();
    }

    public function get(string $key, $fallback = null)
    {
        return Arr::get($this->config, $key, $fallback);
    }

    private function preProcessedConfig()
    {
        $fieldtype = $this->fieldtype();

        $fields = $fieldtype->configFields()->addValues($this->config);

        return array_merge(
            self::commonFieldOptions()->all()->map->defaultValue()->all(),
            $this->config,
            $fields->preProcess()->values()->all(),
            [
                'component' => $fieldtype->component(),
            ]
        );
    }

    public function meta()
    {
        return $this->fieldtype()->preload();
    }

    public function toGql(): array
    {
        $type = $this->fieldtype()->toGqlType();

        if ($type instanceof GqlField) {
            $type = $type->toArray();
        }

        if ($type instanceof Type) {
            $type = ['type' => $type];
        }

        if ($this->isRequired()) {
            $type['type'] = GraphQL::nonNull($type['type']);
        }

        return $type;
    }

    public function isRelationship(): bool
    {
        return $this->fieldtype()->isRelationship();
    }

    public function setForm(Form $form)
    {
        $this->form = $form;

        return $this;
    }

    public function form(): ?Form
    {
        return $this->form;
    }

    public static function commonFieldOptions(): Fields
    {
        $reserved = [
            'content_type',
            'elseif',
            'endif',
            'endunless',
            'if',
            'length',
            'reference',
            'resource',
            'status',
            'unless',
            'views',
        ];

        $fields = collect([
            'display' => [
                'display' => __('Display Label'),
                'instructions' => __('statamic::messages.fields_display_instructions'),
                'type' => 'field_display',
            ],
            'hide_display' => [
                'type' => 'toggle',
                'visibility' => 'hidden',
            ],
            'handle' => [
                'display' => __('Handle'),
                'instructions' => __('statamic::messages.fields_handle_instructions'),
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
            ],
            'instructions' => [
                'display' => __('Instructions'),
                'instructions' => __('statamic::messages.fields_instructions_instructions'),
                'type' => 'textarea',
            ],
            'instructions_position' => [
                'display' => __('Instructions Position'),
                'instructions' => __('statamic::messages.fields_instructions_position_instructions'),
                'type' => 'select',
                'options' => [
                    'above' => __('Above'),
                    'below' => __('Below'),
                ],
                'default' => 'above',
                'if' => [
                    'instructions' => 'not null',
                ],
            ],
            'listable' => [
                'display' => __('Listable'),
                'instructions' => __('statamic::messages.fields_listable_instructions'),
                'type' => 'select',
                'cast_booleans' => true,
                'options' => [
                    'hidden' => __('Hidden by default'),
                    'true' => __('Shown by default'),
                    'false' => __('Not listable'),
                ],
                'default' => 'hidden',
                'unless' => [
                    'type' => 'section',
                ],
            ],
            'sortable' => [
                'display' => __('Sortable'),
                'instructions' => __('statamic::messages.fields_sortable_instructions'),
                'type' => 'toggle',
                'default' => true,
                'unless' => [
                    'visibility' => 'equals computed',
                ],
            ],
            'visibility' => [
                'display' => __('Visibility'),
                'instructions' => __('statamic::messages.fields_visibility_instructions'),
                'options' => [
                    'visible' => __('Visible'),
                    'read_only' => __('Read Only'),
                    'computed' => __('Computed'),
                    'hidden' => __('Hidden'),
                ],
                'default' => 'visible',
                'type' => 'select',
            ],
            'replicator_preview' => [
                'display' => __('Preview'),
                'instructions' => __('statamic::messages.fields_replicator_preview_instructions'),
                'type' => 'toggle',
                'validate' => 'boolean',
                'default' => true,
            ],
            'duplicate' => [
                'display' => __('Duplicate'),
                'instructions' => __('statamic::messages.fields_duplicate_instructions'),
                'type' => 'toggle',
                'validate' => 'boolean',
                'default' => true,
            ],
            'settings_field' => [
                'display' => __('Show in Set Settings'),
                'instructions' => __('statamic::messages.fields_settings_field_instructions'),
                'type' => 'toggle',
                'validate' => 'boolean',
                'default' => false,
            ],
        ])->map(fn ($field, $handle) => compact('handle', 'field'))->values()->all();

        return new ConfigFields($fields);
    }
}
