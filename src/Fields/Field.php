<?php

namespace Statamic\Fields;

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Support\Str;

class Field implements Arrayable
{
    protected $handle;
    protected $prefix;
    protected $config;
    protected $value;
    protected $parent;

    public function __construct($handle, array $config)
    {
        $this->handle = $handle;
        $this->config = $config;
    }

    public function newInstance()
    {
        return (new static($this->handle, $this->config))
            ->setParent($this->parent)
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

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function prefix()
    {
        return $this->prefix;
    }

    public function type()
    {
        return array_get($this->config, 'type', 'text');
    }

    public function fieldtype()
    {
        return FieldtypeRepository::find($this->type())->setField($this);
    }

    public function display()
    {
        return array_get($this->config, 'display', __(Str::slugToTitle($this->handle)));
    }

    public function instructions()
    {
        return array_get($this->config, 'instructions');
    }

    public function rules()
    {
        $rules = [$this->handle => $this->addNullableRule(array_merge(
            $this->get('required') ? ['required'] : [],
            Validator::explodeRules(array_get($this->config, 'validate')),
            Validator::explodeRules($this->fieldtype()->rules())
        ))];

        $extra = collect($this->fieldtype()->extraRules())->map(function ($rules) {
            return $this->addNullableRule(Validator::explodeRules($rules));
        })->all();

        return array_merge($rules, $extra);
    }

    protected function addNullableRule($rules)
    {
        $nullable = true;

        foreach ($rules as $rule) {
            if (preg_match('/^required_?/', $rule)) {
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

    public function isLocalizable()
    {
        return (bool) $this->get('localizable');
    }

    public function isListable()
    {
        if (is_null($this->get('listable'))) {
            return true;
        }

        if ($this->config()['type'] === 'section') {
            return false;
        }

        return (bool) $this->get('listable');
    }

    public function isVisible()
    {
        if (is_null($this->get('listable'))) {
            return in_array($this->handle, ['title', 'slug', 'date', 'author']);
        }

        return ! in_array($this->get('listable'), [false, 'hidden'], true);
    }

    public function isSortable()
    {
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

    public function toPublishArray()
    {
        return array_merge($this->preProcessedConfig(), [
            'handle' => $this->handle,
            'prefix' => $this->prefix,
            'type' => $this->type(),
            'display' => $this->display(),
            'instructions' => $this->instructions(),
            'required' => $this->isRequired(),
        ]);
    }

    public function toBlueprintArray()
    {
        return [
            'handle' => $this->handle,
            'type' => $this->type(),
            'display' => $this->display(),
            'instructions' => $this->instructions(),
            'config' => array_except($this->preProcessedConfig(), 'type'),
        ];
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

    public function get(string $key, $fallback = null)
    {
        return array_get($this->config, $key, $fallback);
    }

    private function preProcessedConfig()
    {
        $fieldtype = $this->fieldtype();

        $fields = $fieldtype->configFields()->addValues($this->config);

        return array_merge($this->config, $fields->preProcess()->values()->all(), [
            'component' => $fieldtype->component(),
        ]);
    }

    public function meta()
    {
        return $this->fieldtype()->preload();
    }

    public function toGraphQL(): \GraphQL\Type\Definition\Type
    {
        $type = $this->fieldtype()->graphQLType();

        if ($this->isRequired()) {
            $type = \GraphQL\Type\Definition\Type::nonNull($type);
        }

        return $type;
    }
}
