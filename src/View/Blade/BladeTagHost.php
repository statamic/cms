<?php

namespace Statamic\View\Blade;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Fields\Value;
use Statamic\Tags\Structure;
use Statamic\Tags\Tags;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;

class BladeTagHost
{
    protected array $params = [];
    protected bool $isPair = false;
    protected array $context = [];
    protected string $method = '';
    protected string $content = '';
    protected mixed $originalValue = null;
    protected mixed $tagValue = null;

    protected ?Tags $tag = null;
    protected array $protectedVariables = ['page'];

    public function __construct(array $context)
    {
        $this->context = $context;
    }

    public function setParams(array $params): static
    {
        $this->params = $params;

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setTag(Tags $tag, string $method): static
    {
        $this->tag = $tag;
        $this->method = $method;

        return $this;
    }

    public function hasProtectedVar(string $name): bool
    {
        return isset($this->context[$name]);
    }

    public function getProtectedVar(string $name): mixed
    {
        return $this->context[$name];
    }

    public function shouldRenderCompiledContent(): bool
    {
        return $this->isAssociativeArray() || $this->tagValue === true;
    }

    public function getDefaultProtectedVariables(): array
    {
        return $this->protectedVariables;
    }

    public function getProtectedVariables(): array
    {
        if ($this->isAssociativeArray()) {
            return array_merge($this->protectedVariables, array_keys($this->tagValue));
        }

        return $this->protectedVariables;
    }

    public function setIsPair(bool $isPair): static
    {
        $this->isPair = $isPair;

        return $this;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function render(): mixed
    {
        $method = $this->method;
        $this->tag->isPair = $this->isPair;
        $this->tag->setContext($this->context);

        $this->tag->setTagRenderer(app(TagRenderer::class));

        if ($this->isPair) {
            $this->tag->setContent($this->content);
            $this->tag->isPair = true;
        }

        $this->originalValue = $this->tag->{$method}();

        return $this->tagValue = self::adjustBladeValue($this->originalValue);
    }

    public function setValue(mixed $value): static
    {
        $this->originalValue = $value;
        $this->tagValue = self::adjustBladeValue($value);

        return $this;
    }

    public function hasScope(): bool
    {
        return array_key_exists('scope', $this->params);
    }

    public function getScopeName(): string
    {
        return $this->params['scope'];
    }

    public function getOriginalValue(): mixed
    {
        return $this->originalValue;
    }

    public function getValue(): mixed
    {
        if (! $this->isPair && ! $this->tagValue) {
            return '';
        }

        if ($this->hasAlias()) {
            return [
                $this->getAlias() => $this->tagValue,
            ];
        }

        if ($this->shouldAddValue()) {
            return $this->addValueKey($this->tagValue);
        }

        return $this->tagValue;
    }

    protected function shouldAddValue(): bool
    {
        $isCandidate = $this->isPair &&
            is_array($this->tagValue) &&
            ! Arr::isAssoc($this->tagValue);

        if (! $isCandidate) {
            return false;
        }

        foreach ($this->tagValue as $value) {
            if (is_array($value) && Arr::isAssoc($value)) {
                return false;
            }

            if ($value instanceof Augmentable || $value instanceof Arrayable) {
                return false;
            }
        }

        return true;
    }

    protected function addValueKey(array $value): array
    {
        return collect($value)
            ->map(fn ($value) => ['value' => $value])
            ->all();
    }

    public function hasTag(): bool
    {
        return $this->tag != null;
    }

    public static function filterParams(array $params): array
    {
        $values = [];

        foreach ($params as $key => $value) {
            if (AntlersNode::isVoidValue($value)) {
                continue;
            }

            $values[$key] = $value;
        }

        return $values;
    }

    public static function adjustBladeValue(mixed $value): mixed
    {
        if ($value instanceof Value) {
            $value = $value->value();
        }

        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if ($value instanceof Augmentable) {
            $value = $value->toDeferredAugmentedArray();
        }

        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        return $value;
    }

    public function getAlias(): string
    {
        return (string) $this->params['as'];
    }

    public function hasAlias(): bool
    {
        return array_key_exists('as', $this->params) && $this->tag instanceof Structure;
    }

    public function isAssociativeArray(): bool
    {
        return is_array($this->getValue()) && Arr::isAssoc($this->getValue());
    }

    public function isArray(): bool
    {
        return is_array($this->tagValue);
    }

    public function isEmpty(): bool
    {
        return count($this->tagValue) === 0;
    }

    public function canRenderAsString(): bool
    {
        return is_string($this->tagValue) || is_numeric($this->tagValue);
    }

    public function renderString(): string
    {
        return (string) $this->tagValue;
    }
}
