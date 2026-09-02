<?php

namespace Statamic\Forms\Fields;

use Facades\Statamic\Fields\FieldtypeRepository;
use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Statamic\Extend\HasHandle;
use Statamic\Extend\RegistersItself;
use Statamic\Facades\Blink;
use Statamic\Fields\Blueprint;
use Statamic\Fields\ConfigFields;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Support\Str;

use function Statamic\trans as __;

abstract class FormFieldtype implements Arrayable
{
    use HasHandle, RegistersItself {
        handle as protected traitHandle;
    }

    protected static $title;
    protected static $fieldtype;
    protected static $extraConfigFields = [];

    protected $field;
    protected $selectable = true;
    protected $description;
    protected $categories = [];
    protected $keywords = [];
    protected $configFields = [];
    protected $icon;
    protected $order;

    public function title(): string
    {
        if (static::$title) {
            return __(static::$title);
        }

        $translation = __($key = 'statamic::form-fieldtypes.'.static::handle().'.title');

        if ($translation !== $key) {
            return $translation;
        }

        return __(Str::title(Str::humanize(static::handle())));
    }

    public function setField(FormField $field)
    {
        $this->field = clone $field;

        return $this;
    }

    public function field(): ?FormField
    {
        return $this->field;
    }

    public static function handle(): string
    {
        return Str::removeRight(static::traitHandle(), '_form_fieldtype');
    }

    public static function fieldtype(): ?string
    {
        return static::$fieldtype;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function categories(): array
    {
        return $this->categories;
    }

    public function collectsValue(): bool
    {
        return ! array_intersect($this->categories(), ['information', 'structure']);
    }

    public function keywords(): array
    {
        return $this->keywords;
    }

    public function icon(): string
    {
        return $this->icon ?? "form-field-{$this->handle()}";
    }

    public function order(): ?int
    {
        return $this->order;
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

    public function configFields(): Fields
    {
        if ($cached = Blink::get($blink = 'form-config-fields-'.$this->handle())) {
            return $cached;
        }

        $fields = collect($this->configFieldItems())
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

    public function extraConfigFieldItems(): array
    {
        return array_merge(
            $this->configFieldItemsFromWrappedFieldtype(),
            self::$extraConfigFields[static::class] ?? [],
            self::$extraConfigFields[FormFieldtype::class] ?? [],
        );
    }

    // TODO: Remove this bridge in v7, once addons have migrated to FormFieldtype::appendConfigField.
    private function configFieldItemsFromWrappedFieldtype(): array
    {
        if (! $handle = static::fieldtype()) {
            return [];
        }

        if (! $class = FieldtypeRepository::classes()->get($handle)) {
            return [];
        }

        return app($class)->extraConfigFieldItems();
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

    public function configBlueprint(): Blueprint
    {
        return (new Blueprint)->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => collect($this->configFieldItems())
                                ->merge($this->extraConfigFieldItems())
                                ->map(fn ($field, $handle) => compact('handle', 'field'))
                                ->values()->all(),
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function toField(): Field
    {
        return new Field($this->handle(), $this->toFieldArray());
    }

    abstract public function toFieldArray(): array;

    public function example(): ?array
    {
        return null;
    }

    public function defaultChart(): ?string
    {
        return null;
    }

    public function chartOptions(Collection $values): ?Collection
    {
        return null;
    }

    public function insights(): array
    {
        return [];
    }

    public function view(): string
    {
        $handle = $this->handle();
        $language = config('statamic.templates.language', 'antlers');

        $views = [
            "statamic::forms.fields.{$handle}",
            "statamic::forms.{$language}.fields.{$handle}",
        ];

        if ($underlyingFieldtype = static::fieldtype()) {
            $views = [
                "statamic::forms.fields.{$underlyingFieldtype}",
                "statamic::forms.{$language}.fields.{$underlyingFieldtype}",
                ...$views,
            ];
        }

        return collect($views)->first(fn (string $view): bool => view()->exists($view))
            ?? $this->toField()->fieldtype()->view();
    }

    public function isSelectable(): bool
    {
        if (FormFieldtypeRepository::selectableIsOverriden($this->handle())) {
            return FormFieldtypeRepository::hasBeenMadeSelectable($this->handle());
        }

        return $this->selectable;
    }

    public static function makeSelectable(): void
    {
        FormFieldtypeRepository::makeSelectable(static::handle());
    }

    public static function makeUnselectable(): void
    {
        FormFieldtypeRepository::makeUnselectable(static::handle());
    }

    public function toArray(): array
    {
        return [
            'handle' => $this->handle(),
            'title' => $this->title(),
            'description' => $this->description(),
            'categories' => $this->categories(),
            'keywords' => $this->keywords(),
            'icon' => $this->icon(),
            'order' => $this->order(),
            'config' => $this->configFields()->toPublishArray(),
        ];
    }
}
