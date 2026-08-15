<?php

namespace Statamic\Tags;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use RuntimeException;
use Statamic\Facades\Cascade;
use Statamic\Fields\Value;
use Statamic\Support\Arr;
use Statamic\Tags\Concerns\RendersViews;
use Statamic\View\Antlers\Engine;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Slot;

class IncludeTag extends Tags
{
    use RendersViews;

    public const CONTEXT_KEY = '__statamic_include';

    public const SLOTS_KEY = '__statamic_include_slots';

    public const SLOT_PARAM_PREFIX = '__statamic_include_slot_';

    public const VIEW_DATA_KEYS = [
        self::CONTEXT_KEY,
        self::SLOTS_KEY,
    ];

    private const CONTROL = [
        'src',
        'when',
        'unless',
        'cascade',
        'params',
        'handle_prefix',
    ];

    private const RESERVED = [
        '__frontmatter',
        self::CONTEXT_KEY,
        self::SLOTS_KEY,
    ];

    private const PROTECTED_ALIASES = [
        'app',
        'errors',
        'params',
        'view',
    ];

    protected static $handle = 'include';

    public static $isolated = true;

    public function wildcard($tag)
    {
        $view = $this->params->get('src', $tag);

        if (! $view) {
            throw new RuntimeException('The include tag requires a view name or the [src] parameter.');
        }

        return $this->render($view);
    }

    protected function render($view)
    {
        $parameters = $this->params->all();
        $spread = $this->unwrap($parameters['params'] ?? null);
        $prefixes = $this->unwrap($parameters['handle_prefix'] ?? null);

        $this->validateReserved($parameters, $spread, $prefixes);

        if (! $this->shouldRender()) {
            return '';
        }

        $data = $this->resolveData($parameters, $this->spread($spread), $prefixes);
        $view = view($this->viewName($view));
        $isBlade = ! Str::endsWith($view->getPath(), Engine::EXTENSIONS);

        $cascade = Cascade::toArray();

        $scope = array_merge(
            $this->params->bool('cascade') ? $cascade : [],
            $data,
            $this->resolveSlots($parameters, $data, $isBlade),
            [
                'params' => $data,
                '__frontmatter' => $data,
            ],
            $isBlade ? [self::CONTEXT_KEY => true] : []
        );

        $hadViews = array_key_exists('views', $cascade);
        $viewsState = $cascade['views'] ?? null;

        // Suspended here rather than in the runtime's isolation so Blade-invoked includes are
        // isolated too. Other isolated tags inheriting handle prefixes is technically
        // unintentional, but preserved for BC. This may change in the next major version.
        $suspendedCascade = GlobalRuntimeState::$isCascadeEnabled;
        $suspendedPrefixes = GlobalRuntimeState::$prefixState;

        GlobalRuntimeState::$isCascadeEnabled = false;
        GlobalRuntimeState::$prefixState = [];

        try {
            return $view->with($scope)
                ->withoutExtractions()
                ->render();
        } finally {
            GlobalRuntimeState::$isCascadeEnabled = $suspendedCascade;
            GlobalRuntimeState::$prefixState = $suspendedPrefixes;

            if ($hadViews) {
                Cascade::set('views', $viewsState);
            } elseif (Cascade::get('views') !== null) {
                Cascade::data(Arr::except(Cascade::toArray(), 'views'));
            }
        }
    }

    protected function resolveData(array $parameters, array $spread, mixed $prefixes): array
    {
        $named = [];

        foreach ($parameters as $key => $value) {
            if ($this->isDataParameter($key, $value)) {
                $named[$key] = $value;
            }
        }

        return array_merge(
            $spread,
            $this->unprefixedAliases($spread, $prefixes),
            $named,
            $this->unprefixedAliases($named, $prefixes)
        );
    }

    protected function resolveSlots(array $parameters, array $data, bool $isBlade): array
    {
        $slots = [];

        foreach ($parameters as $key => $value) {
            if ($this->isSlotParameter($key, $value)) {
                $slots[substr($key, strlen(self::SLOT_PARAM_PREFIX))] = $value;
            }
        }

        if ($this->isolatedContext === null && $this->isPair && ! isset($slots['slot'])) {
            $content = $this->getSlotContent();

            if ((string) $content !== '') {
                $slots['slot'] = $content;
            }
        }

        if (empty($slots)) {
            return [];
        }

        $normalized = [];
        $namedSlots = [];

        foreach ($slots as $name => $slot) {
            if ($slot instanceof Slot) {
                $slot->withParams($data);
            }

            if ($name === 'slot') {
                $normalized['slot'] = $slot;

                continue;
            }

            $normalized['slot:'.$name] = $slot;
            $namedSlots[$name] = $slot;

            if ($isBlade && $this->canAliasSlot($name, $data)) {
                $normalized[$name] = $slot;
            }
        }

        if ($isBlade) {
            $normalized[self::SLOTS_KEY] = $namedSlots;
        }

        if (! empty($namedSlots)) {
            $normalized[GlobalRuntimeState::createIndicatorVariable(
                GlobalRuntimeState::INDICATOR_NAMED_SLOTS_AVAILABLE
            )] = true;
        }

        return $normalized;
    }

    protected function canAliasSlot(string $name, array $data): bool
    {
        return ! array_key_exists($name, $data)
            && ! str_starts_with($name, '__')
            && ! in_array($name, self::PROTECTED_ALIASES);
    }

    protected function isDataParameter(int|string $key, mixed $value): bool
    {
        return ! in_array($key, self::CONTROL)
            && ! in_array($key, self::RESERVED)
            && ! $this->isSlotParameter($key, $value);
    }

    protected function isSlotParameter(int|string $key, mixed $value): bool
    {
        return $this->hasSlotPrefix($key) && $value instanceof Slot;
    }

    protected function hasSlotPrefix(int|string $key): bool
    {
        return is_string($key) && str_starts_with($key, self::SLOT_PARAM_PREFIX);
    }

    protected function isPrefixedKey(int|string $key, string $prefix): bool
    {
        return is_string($key) && str_starts_with($key, $prefix) && strlen($key) > strlen($prefix);
    }

    protected function spread(mixed $spread): array
    {
        if ($spread === null) {
            return [];
        }

        if (! is_array($spread) || (! empty($spread) && ! Arr::isAssoc($spread))) {
            throw new RuntimeException('The [params] parameter on the include tag must be an associative array.');
        }

        return $spread;
    }

    protected function unprefixedAliases(array $data, mixed $prefixes): array
    {
        $aliases = [];

        foreach (array_reverse(Arr::wrap($prefixes)) as $prefix) {
            if (! is_string($prefix) || $prefix === '') {
                continue;
            }

            foreach ($data as $key => $value) {
                if ($this->isPrefixedKey($key, $prefix)) {
                    $aliases[substr($key, strlen($prefix))] = $value;
                }
            }
        }

        return $aliases;
    }

    protected function validateReserved(array $parameters, mixed $spread, mixed $prefixes): void
    {
        $this->validateKeys($parameters, allowSlots: true);

        if (! is_array($spread)) {
            return;
        }

        $this->validateKeys($spread);
        $this->validateKeys($this->unprefixedAliases($spread, $prefixes));
        $this->validateKeys($this->unprefixedAliases(Arr::except($parameters, self::CONTROL), $prefixes));
    }

    protected function validateKeys(array $parameters, bool $allowSlots = false): void
    {
        foreach ($parameters as $key => $value) {
            $allowedSlot = $allowSlots && $this->isSlotParameter($key, $value);

            if (in_array($key, self::RESERVED) || ($this->hasSlotPrefix($key) && ! $allowedSlot)) {
                throw new RuntimeException("Cannot pass reserved parameter [{$key}] to the include tag.");
            }
        }
    }

    protected function unwrap(mixed $value): mixed
    {
        if ($value instanceof Value) {
            $value = $value->value();
        }

        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        return $value;
    }
}
