<?php

namespace Statamic\View\Scaffolding\Emitters;

use Closure;
use Illuminate\Support\Collection;
use Statamic\Facades\Blueprint as Blueprints;
use Statamic\Fields\Blueprint;

class BladeSourceEmitter extends AbstractSourceEmitter
{
    protected static array $variableStack = [];
    protected static ?string $currentIterationVar = null;
    protected static array $variableCounters = [];
    protected static array $stackHistory = [];
    protected static bool $ignoreStack = false;

    public function pushContext(string $varName, bool $isIteration = false): static
    {
        static::$variableStack[] = [
            'var' => $varName,
            'iteration' => $isIteration,
        ];

        if ($isIteration) {
            static::$currentIterationVar = $varName;
        }

        return $this;
    }

    public function popContext(): static
    {
        array_pop(static::$variableStack);

        static::$currentIterationVar = null;
        foreach (array_reverse(static::$variableStack) as $context) {
            if ($context['iteration']) {
                static::$currentIterationVar = $context['var'];
                break;
            }
        }

        return $this;
    }

    public function getCurrentRoot(): ?string
    {
        if (static::$currentIterationVar) {
            return static::$currentIterationVar;
        }

        if (! empty(static::$variableStack)) {
            return end(static::$variableStack)['var'];
        }

        return null;
    }

    public function getVariableStack(): array
    {
        return static::$variableStack;
    }

    public static function resetStack(): void
    {
        static::$variableStack = [];
        static::$currentIterationVar = null;
        static::$variableCounters = [];
        static::$stackHistory = [];
        static::$ignoreStack = false;
        static::$blueprintStack = [];
    }

    public function isolate(Closure $callback): string
    {
        $previousIgnoreStack = static::$ignoreStack;
        static::$ignoreStack = true;

        try {
            $emit = $this->createInstance();
            $inner = trim((string) $callback($emit));

            return $this->normalizeLineEndings($inner);
        } finally {
            static::$ignoreStack = $previousIgnoreStack;
        }
    }

    protected function pushStackState(): void
    {
        static::$stackHistory[] = [
            'stack' => static::$variableStack,
            'currentIterationVar' => static::$currentIterationVar,
        ];

        static::$variableStack = [];
        static::$currentIterationVar = null;
    }

    protected function popStackState(): void
    {
        if (empty(static::$stackHistory)) {
            return;
        }

        $previous = array_pop(static::$stackHistory);
        static::$variableStack = $previous['stack'];
        static::$currentIterationVar = $previous['currentIterationVar'];
    }

    public function getCountedVariable(string $baseName): string
    {
        if (! isset(static::$variableCounters[$baseName])) {
            static::$variableCounters[$baseName] = 0;

            return $baseName;
        }

        static::$variableCounters[$baseName]++;

        return $baseName.static::$variableCounters[$baseName];
    }

    public function releaseCountedVariable(string $baseName): void
    {
        if (! isset(static::$variableCounters[$baseName])) {
            return;
        }

        static::$variableCounters[$baseName]--;

        if (static::$variableCounters[$baseName] < 0) {
            unset(static::$variableCounters[$baseName]);
        }
    }

    public function getVariableCounter(string $baseName): int
    {
        return static::$variableCounters[$baseName] ?? -1;
    }

    public function getVariableCounters(): array
    {
        return static::$variableCounters;
    }

    public function withCountedVariable(string|array $baseName, Closure $callback): mixed
    {
        if (is_array($baseName)) {
            $varNames = [];
            $baseNames = $baseName;

            foreach ($baseNames as $name) {
                $varNames[] = $this->getCountedVariable($name);
            }

            try {
                return $callback(...$varNames);
            } finally {
                foreach (array_reverse($baseNames) as $name) {
                    $this->releaseCountedVariable($name);
                }
            }
        }

        $varName = $this->getCountedVariable($baseName);

        try {
            return $callback($varName);
        } finally {
            $this->releaseCountedVariable($baseName);
        }
    }

    public function varName(string $variable): string
    {
        return $this->buildVariablePath($variable);
    }

    protected function buildVariablePath(string $name): string
    {
        if (static::$ignoreStack || empty(static::$variableStack)) {
            return $this->convertDotsToArrows($name);
        }

        $contextPath = $this->buildContextPath();

        if (str_contains($name, '.')) {
            $parts = explode('.', $name);
            $firstPart = $parts[0];

            $firstPartInStack = false;
            foreach (static::$variableStack as $context) {
                if ($context['var'] === $firstPart) {
                    $firstPartInStack = true;
                    break;
                }
            }

            if ($firstPartInStack) {
                return $this->convertDotsToArrows($name);
            }

            $accessor = $contextPath;

            foreach ($parts as $part) {
                $accessor .= "->{$part}";
            }

            return $accessor;
        }

        return "{$contextPath}->{$name}";
    }

    protected function buildContextPath(): string
    {
        if (empty(static::$variableStack)) {
            return '';
        }

        $iterationIndex = $this->findLastIterationIndex();
        $startIndex = max($iterationIndex, 0);

        $path = '$'.(static::$variableStack[$startIndex]['var']);

        for ($i = $startIndex + 1; $i < count(static::$variableStack); $i++) {
            $path .= '->'.(static::$variableStack[$i]['var']);
        }

        return $path;
    }

    public function html(string $name, bool $indent = false, bool $newLine = false): static
    {
        return $this->variable(
            $name,
            $indent,
            $newLine,
            '{!!',
            '!!}',
        );
    }

    public function variable(string $name, bool $indent = false, bool $newLine = false, string $start = '{{', string $end = '}}'): static
    {
        $variable = "{$start} {$this->buildVariablePath($name)} {$end}";

        $this->optionalNewline();

        if ($indent) {
            $variable = $this->indentText($variable);
        }

        $this->content .= $variable;

        return $this;
    }

    public function pair(string $name, Closure $content): string
    {
        $emit = $this->createInstance();

        $inner = trim($this->withStackCleanup(fn () => $content($emit)));

        $indented = $this->indentText($inner);

        return $this->normalizeLineEndings("<s:{$name}>\n{$indented}\n</s:{$name}>");
    }

    public function withContext(string $name, Closure $content)
    {
        if (str_starts_with($name, '$')) {
            $name = substr($name, 1);
        }

        $inner = $this->executeWithContext($name, $content, isIteration: false);

        return $this->normalizeLineEndings($inner);
    }

    public function withIsolatedContext(string $varName, Closure $callback, bool $isIteration = false): string
    {
        $this->pushStackState();

        try {
            $emit = $this->createInstance();
            $emit->pushContext($varName, isIteration: $isIteration);

            try {
                $inner = trim((string) $callback($emit));
            } finally {
                $emit->popContext();
            }

            return $this->normalizeLineEndings($inner);
        } finally {
            $this->popStackState();
        }
    }

    public function withIsolatedIteration(string $varName, Closure $callback): string
    {
        return $this->withIsolatedContext($varName, $callback, isIteration: true);
    }

    public function pairWithContext(string $name, Closure $content): string
    {
        $inner = $this->executeWithContext($name, $content, isIteration: false);
        $indented = $this->indentText($inner);

        return $this->normalizeLineEndings("<s:{$name}>\n{$indented}\n</s:{$name}>");
    }

    public function forEach(string $variable, string $value = 'value', ?string $key = null, Closure|string $content = ''): string
    {
        $value = ltrim($value, '$');
        $key = $key ? ltrim($key, '$') : null;

        $varPath = $this->buildVariablePath($variable);

        if ($key !== null) {
            $declaration = "@foreach ({$varPath} as \${$key} => \${$value})";
        } else {
            $declaration = "@foreach ({$varPath} as \${$value})";
        }

        if ($content instanceof Closure) {
            $inner = $this->executeWithContext($value, $content, isIteration: true);
        } else {
            $inner = trim($content);
        }

        $indented = $this->indentText($inner);

        return $this->normalizeLineEndings("{$declaration}\n{$indented}\n@endforeach");
    }

    public function tag(string $name, array $params = []): string
    {
        $paramChain = $this->buildParamsChain($params);

        return <<<BLADE_TAG
Statamic::tag('$name'){$paramChain}->fetch()
BLADE_TAG;

    }

    protected function buildParamsChain(array $params): string
    {
        if (empty($params)) {
            return '';
        }

        $items = [];

        foreach ($params as $key => $value) {
            $items[] = "$key($value)";
        }

        return '->'.implode('->', $items);
    }

    public function comment(string $text): static
    {
        $this->optionalNewline();
        $this->content .= "{{-- {$text} --}}";

        return $this;
    }

    public function variables(string ...$vars): static
    {
        $this->optionalNewline();

        foreach ($vars as $var) {
            $this->content .= "{{ {$this->buildVariablePath($var)} }}\n";
        }

        $this->content = rtrim($this->content);

        return $this;
    }

    public function properties(string $root, array $properties, bool $fromStackRoot = false): static
    {
        $this->optionalNewline();

        foreach ($properties as $property) {
            $accessor = $this->buildPropertyAccessor($root, $property);
            $this->content .= "{{ {$accessor} }}\n";
        }

        $this->content = rtrim($this->content);

        return $this;
    }

    public function keys(string $root, array $keys, bool $fromStackRoot = false): static
    {
        $this->optionalNewline();

        foreach ($keys as $key) {
            $accessor = $this->buildPropertyAccessor($root, "[$key]");

            $this->content .= "{{ {$accessor} }}\n";
        }

        $this->content = rtrim($this->content);

        return $this;
    }

    protected function buildPropertyAccessor(string $root, string $property): string
    {
        if (! empty(static::$variableStack)) {
            if (static::$currentIterationVar) {
                $accessor = '$'.(static::$currentIterationVar);

                $iterationIndex = $this->findLastIterationIndex();

                if ($iterationIndex >= 0) {
                    for ($i = $iterationIndex + 1; $i < count(static::$variableStack); $i++) {
                        $accessor .= '->'.(static::$variableStack[$i]['var']);
                    }
                }

                if ($root !== static::$currentIterationVar) {
                    $accessor .= "->{$root}";
                }
            } else {
                $accessor = '$'.(static::$variableStack[0]['var']);

                for ($i = 1; $i < count(static::$variableStack); $i++) {
                    $accessor .= '->'.(static::$variableStack[$i]['var']);
                }

                $accessor .= "->{$root}";
            }
        } else {
            $accessor = "\${$root}";
        }
        $segments = [];

        if (str_starts_with($property, '[')) {
            $segments = $this->parseBracketNotation($property);

            foreach ($segments as $segment) {
                $accessor .= "['{$segment}']";
            }

            return $accessor;
        }

        $dotParts = explode('.', $property);

        foreach ($dotParts as $part) {
            if (str_contains($part, '[')) {
                preg_match('/^([^\[]+)?(.*)$/', $part, $matches);
                $propName = $matches[1] ?? '';
                $brackets = $matches[2] ?? '';

                if ($propName !== '') {
                    $accessor .= "->{$propName}";
                }

                if ($brackets) {
                    $keys = $this->parseBracketNotation($brackets);
                    foreach ($keys as $key) {
                        $accessor .= "['{$key}']";
                    }
                }
            } else {
                $accessor .= "->{$part}";
            }
        }

        return $accessor;
    }

    protected function parseBracketNotation(string $notation): array
    {
        preg_match_all('/\[([^\]]+)\]/', $notation, $matches);

        return $matches[1] ?? [];
    }

    protected function convertDotsToArrows(string $name, string $prefix = '$'): string
    {
        if (! str_contains($name, '.')) {
            return "{$prefix}{$name}";
        }

        $parts = explode('.', $name);
        $accessor = $prefix.array_shift($parts);

        foreach ($parts as $part) {
            $accessor .= "->{$part}";
        }

        return $accessor;
    }

    protected function findLastIterationIndex(): int
    {
        $iterationIndex = -1;

        foreach (static::$variableStack as $index => $context) {
            if ($context['iteration']) {
                $iterationIndex = $index;
            }
        }

        return $iterationIndex;
    }

    protected function executeWithContext(string $name, Closure $content, bool $isIteration = false): string
    {
        $emit = $this->createInstance();
        $emit->pushContext($name, isIteration: $isIteration);

        try {
            return trim((string) $content($emit));
        } finally {
            $emit->popContext();
        }
    }

    protected function getConditionBuilder(): ConditionBuilder
    {
        return new ConditionBuilder(
            ifTemplate: "@if({condition})\n{template}\n",
            elseifTemplate: "@elseif({condition})\n{template}\n",
            endTemplate: '@endif',
        );
    }

    public function fields(array|Collection $fields): static
    {
        $this->optionalNewline();

        $this->content .= $this->generator()->scaffoldFields($fields);

        return $this;
    }

    public function blueprint(Blueprint|string|null $blueprint, ?Closure $fallback = null): static
    {
        if (! $blueprint) {
            return $this;
        }

        if (is_string($blueprint)) {
            $blueprintHandle = $blueprint;
            $blueprint = Blueprints::find($blueprint);
        } else {
            $blueprintHandle = $blueprint->handle();
        }

        if (! $blueprint) {
            return $this;
        }

        if (in_array($blueprintHandle, static::$blueprintStack)) {
            if ($fallback) {
                $this->content .= $fallback($this->createInstance());
            }

            return $this;
        }

        static::$blueprintStack[] = $blueprintHandle;

        try {
            $this->optionalNewline();
            $this->content .= $this->generator()->scaffoldBlueprint($blueprint);
        } finally {
            array_pop(static::$blueprintStack);
        }

        return $this;
    }
}
