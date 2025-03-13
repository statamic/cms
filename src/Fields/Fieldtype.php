<?php

namespace Statamic\Fields;

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Extend\HasHandle;
use Statamic\Extend\RegistersItself;
use Statamic\Facades\Blink;
use Statamic\Facades\GraphQL;
use Statamic\Query\Scopes\Filters\Fields\FieldtypeFilter;
use Statamic\Statamic;
use Statamic\Support\Str;

use function Statamic\trans as __;

abstract class Fieldtype implements Arrayable
{
    use HasHandle, RegistersItself {
        handle as protected traitHandle;
    }

    protected static $title;
    protected static $binding = 'fieldtypes';

    protected $field;
    protected $localizable = true;
    protected $validatable = true;
    protected $defaultable = true;
    protected $selectable = true;
    protected $selectableInForms = false;
    protected $relationship = false;
    protected $categories = [];
    protected $keywords = [];
    protected $rules = [];
    protected $extraRules = [];
    protected $defaultValue;
    protected $configFields = [];
    protected static $extraConfigFields = [];
    protected $icon;

    public static function title()
    {
        if (static::$title) {
            return __(static::$title);
        }

        $translation = __($key = 'statamic::fieldtypes.'.static::handle().'.title');

        if ($translation !== $key) {
            return $translation;
        }

        return __(Str::title(Str::humanize(static::handle())));
    }

    public function setField(Field $field)
    {
        $this->field = clone $field;

        return $this;
    }

    public function field(): ?Field
    {
        return $this->field;
    }

    public static function handle()
    {
        return Str::removeRight(static::traitHandle(), '_fieldtype');
    }

    public function component(): string
    {
        return $this->component ?? static::handle();
    }

    public function indexComponent(): string
    {
        return $this->indexComponent ?? static::handle();
    }

    public function localizable(): bool
    {
        return $this->localizable;
    }

    public function validatable(): bool
    {
        return $this->validatable;
    }

    public function defaultable(): bool
    {
        return $this->defaultable;
    }

    public function selectable(): bool
    {
        return $this->selectable;
    }

    public function selectableInForms(): bool
    {
        return $this->selectableInForms ?: FieldtypeRepository::hasBeenMadeSelectableInForms($this->handle());
    }

    public static function makeSelectableInForms()
    {
        FieldtypeRepository::makeSelectableInForms(self::handle());
    }

    public function categories(): array
    {
        return $this->categories;
    }

    public function keywords(): array
    {
        return $this->keywords;
    }

    public function filter()
    {
        return new FieldtypeFilter($this);
    }

    public function rules(): array
    {
        return Validator::explodeRules($this->rules);
    }

    public function fieldRules()
    {
        return $this->config('validate');
    }

    public function extraRules(): array
    {
        return array_map([Validator::class, 'explodeRules'], $this->extraRules);
    }

    public function extraValidationAttributes(): array
    {
        return [];
    }

    public function preProcessValidatable($value)
    {
        return $value;
    }

    public function preProcessTagRenderable($data, $recursiveCallback)
    {
        return $data;
    }

    public function defaultValue()
    {
        return $this->defaultValue;
    }

    public function augment($value)
    {
        return $value;
    }

    public function shallowAugment($value)
    {
        return $this->augment($value);
    }

    public function toArray(): array
    {
        return [
            'handle' => $this->handle(),
            'title' => $this->title(),
            'localizable' => $this->localizable(),
            'validatable' => $this->validatable(),
            'defaultable' => $this->defaultable(),
            'categories' => $this->categories(),
            'keywords' => $this->keywords(),
            'icon' => $this->icon(),
            'config' => $this->configFields()->toPublishArray(),
        ];
    }

    public function configBlueprint(): Blueprint
    {
        return (new Blueprint)->setContents([
            'tabs' => [
                'main' => [
                    'sections' => $this->configSections(),
                ],
            ],
        ]);
    }

    private function configSections()
    {
        $fields = $this->configFieldItems();
        $extras = $this->extraConfigFieldItems();

        if (empty($fields) && empty($extras)) {
            return [];
        }

        $extras = collect($extras)
            ->map(fn ($field, $handle) => compact('handle', 'field'))
            ->values()->all();

        if (! $this->configFieldsUseSections()) {
            return [
                [
                    'fields' => collect($fields)
                        ->map(fn ($field, $handle) => compact('handle', 'field'))
                        ->merge($extras)
                        ->values()->all(),
                ],
            ];
        }

        $sections = collect($fields)->map(function ($section) {
            $section['fields'] = collect($section['fields'])
                ->map(fn ($field, $handle) => compact('handle', 'field'))
                ->values()->all();

            return $section;
        });

        if (! empty($extras)) {
            if ($sections->containsOneItem()) {
                $section = $sections[0];
                $section['fields'] = array_merge($section['fields'], $extras);
                $sections[0] = $section;
            } else {
                $sections[] = ['fields' => $extras];
            }
        }

        return $sections->all();
    }

    private function configFieldsUseSections()
    {
        if (empty($fields = $this->configFieldItems())) {
            return false;
        }

        return array_keys($fields)[0] === 0;
    }

    public function configFields(): Fields
    {
        if ($cached = Blink::get($blink = 'config-fields-'.$this->handle())) {
            return $cached;
        }

        $fields = collect($this->configFieldItems());

        if ($this->configFieldsUseSections()) {
            $fields = $fields->flatMap(fn ($section) => $section['fields']);
        }

        $fields = $fields
            ->merge($this->extraConfigFieldItems())
            ->map(function ($field, $handle) {
                return compact('handle', 'field');
            });

        $fields = new ConfigFields($fields);

        Blink::put($blink, $fields);

        return $fields;
    }

    protected function configFieldItems(): array
    {
        return $this->configFields;
    }

    protected function extraConfigFieldItems(): array
    {
        return self::$extraConfigFields[static::class] ?? [];
    }

    public static function appendConfigFields(array $config): void
    {
        $existingConfig = self::$extraConfigFields[static::class] ?? [];

        self::$extraConfigFields[static::class] = array_merge($existingConfig, $config);
    }

    public static function appendConfigField(string $field, array $config): void
    {
        self::appendConfigFields([$field => $config]);
    }

    public function icon()
    {
        return $this->icon ?? $this->handle();
    }

    public function process($data)
    {
        return $data;
    }

    public function preProcess($data)
    {
        return $data;
    }

    public function preProcessConfig($data)
    {
        return $this->preProcess($data);
    }

    public function preProcessIndex($data)
    {
        return $data;
    }

    public function view()
    {
        $default = 'statamic::forms.fields.'.$this->handle();

        return view()->exists($default)
            ? $default
            : 'statamic::forms.fields.default';
    }

    public function config(?string $key = null, $fallback = null)
    {
        if (! $this->field) {
            return $fallback;
        }

        $config = $this->configFields()->all()
            ->map->defaultValue()
            ->merge($this->field->config());

        return $key
            ? ($config->get($key) ?? $fallback)
            : $config->all();
    }

    public function preload()
    {
        //
    }

    public static function preloadable()
    {
        return static::$preloadable ?? (new \ReflectionClass(static::class))->getMethod('preload')->class === static::class;
    }

    public static function docsUrl()
    {
        return Statamic::docsUrl('fieldtypes/'.static::handle());
    }

    public function toGqlType()
    {
        return GraphQL::string();
    }

    public function addGqlTypes()
    {
        //
    }

    public function isRelationship(): bool
    {
        return $this->relationship;
    }

    public function toQueryableValue($value)
    {
        return $value;
    }

    public function extraRenderableFieldData(): array
    {
        return [];
    }

    public function hasJsDriverDataBinding(): bool
    {
        return true;
    }
}
