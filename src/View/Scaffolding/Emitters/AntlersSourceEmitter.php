<?php

namespace Statamic\View\Scaffolding\Emitters;

use Closure;
use Illuminate\Support\Collection;

class AntlersSourceEmitter extends AbstractSourceEmitter
{
    protected static array $variableStack = [];
    protected static ?string $currentIterationVar = null;
    protected static array $variableCounters = [];
    protected static array $stackHistory = [];
    protected static bool $ignoreStack = false;

    public function append(string $text): static
    {
        $this->content .= $text;

        return $this;
    }

    public function variable(string $name, bool $selfClosing = true, bool $indent = false, bool $newLine = false): static
    {
        $suffix = $selfClosing ? ' /' : ' ';
        $varPath = $this->buildVariablePath($name);
        $variable = "{{ {$varPath}{$suffix}}}";

        if ($newLine) {
            $this->optionalNewline();
        }

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

        return $this->content.$this->normalizeLineEndings("{{ {$name} }}\n{$indented}\n{{ /{$name} }}");
    }

    public function tag(string $name, Closure $content, array $params = []): string
    {
        if ($this->preferComponentSyntax) {
            return $this->component($name, $content, $params);
        }

        $emit = $this->createInstance();

        $inner = trim($this->withStackCleanup(fn () => $content($emit)));

        $indented = $this->indentText($inner);

        $opening = "{{ {$name}";

        foreach ($params as $key => $value) {
            $opening .= " {$key}=\"{$value}\"";
        }

        $opening .= ' }}';

        return $this->normalizeLineEndings("{$opening}\n{$indented}\n{{ /{$name} }}");
    }

    public function withContext(string $name, Closure $content): string
    {
        $inner = $this->executeWithContext($name, $content, isIteration: false);

        if (strlen($this->content) > 0 && ! str_ends_with($this->content, "\n")) {
            $inner = "\n".$inner;
        }

        return $this->normalizeLineEndings($inner);
    }

    public function comment(string $text): static
    {
        $this->optionalNewline();
        $this->content .= "{{# {$text} #}}";

        return $this;
    }

    public function variables(string ...$vars): static
    {
        $this->optionalNewline();

        foreach ($vars as $var) {
            $varPath = $this->buildVariablePath($var);
            $this->content .= "{{ {$varPath} /}}\n";
        }

        $this->content = rtrim($this->content);

        return $this;
    }

    public function varName(string $variable): string
    {
        return $this->buildVariablePath($variable);
    }

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

    protected function buildVariablePath(string $name): string
    {
        if (static::$ignoreStack || empty(static::$variableStack)) {
            return $name;
        }

        $contextPath = $this->buildContextPath();

        $firstPart = explode(':', $name)[0];
        $firstPartInStack = false;
        foreach (static::$variableStack as $context) {
            if ($context['var'] === $firstPart && str_contains($name, ':')) {
                $firstPartInStack = true;
                break;
            }
        }

        if ($firstPartInStack) {
            return $name;
        }

        if ($contextPath) {
            return "{$contextPath}:{$name}";
        }

        return $name;
    }

    protected function buildContextPath(): string
    {
        if (empty(static::$variableStack)) {
            return '';
        }

        $iterationIndex = $this->findLastIterationIndex();
        $startIndex = $iterationIndex >= 0 ? $iterationIndex : 0;

        $path = static::$variableStack[$startIndex]['var'];

        for ($i = $startIndex + 1; $i < count(static::$variableStack); $i++) {
            $path .= ':'.static::$variableStack[$i]['var'];
        }

        return $path;
    }

    public function properties(string $root, array $properties, bool $fromStackRoot = false): static
    {
        $this->optionalNewline();

        foreach ($properties as $property) {
            $accessor = $this->buildAntlersAccessor($root, $property);
            $this->content .= "{{ {$accessor} /}}\n";
        }

        $this->content = rtrim($this->content);

        return $this;
    }

    public function keys(string $root, array $keys, bool $fromStackRoot = false): static
    {
        $this->optionalNewline();

        foreach ($keys as $key) {
            $accessor = $this->buildAntlersAccessor($root, "[$key]");
            $this->content .= "{{ {$accessor} /}}\n";
        }

        $this->content = rtrim($this->content);

        return $this;
    }

    protected function buildAntlersAccessor(string $root, string $property): string
    {
        if (! empty(static::$variableStack)) {
            if (static::$currentIterationVar) {
                $accessor = static::$currentIterationVar;

                $iterationIndex = $this->findLastIterationIndex();

                if ($iterationIndex >= 0) {
                    for ($i = $iterationIndex + 1; $i < count(static::$variableStack); $i++) {
                        $accessor .= ':'.static::$variableStack[$i]['var'];
                    }
                }

                if ($root !== '' && $root !== static::$currentIterationVar) {
                    $accessor .= ":{$root}";
                }
            } else {
                $accessor = static::$variableStack[0]['var'];

                for ($i = 1; $i < count(static::$variableStack); $i++) {
                    $accessor .= ':'.static::$variableStack[$i]['var'];
                }

                if ($root !== '') {
                    $accessor .= ":{$root}";
                }
            }
        } else {
            $accessor = $root;
        }

        if (str_starts_with($property, '[')) {
            $property = $this->ensureBracketQuotes($property);

            return $accessor.$property;
        }

        if (str_contains($property, '[')) {
            preg_match('/^([^\[]+)(.*)$/', $property, $matches);
            $propPart = $matches[1] ?? '';
            $bracketPart = $matches[2] ?? '';

            $propPart = str_replace('.', ':', $propPart);

            $bracketPart = $this->ensureBracketQuotes($bracketPart);

            if ($accessor === '') {
                return $propPart.$bracketPart;
            }

            return "{$accessor}:{$propPart}{$bracketPart}";
        }

        $property = str_replace('.', ':', $property);

        if ($accessor === '') {
            return $property;
        }

        return "{$accessor}:{$property}";
    }

    protected function ensureBracketQuotes(string $notation): string
    {
        if (preg_match("/\[['\"]/", $notation)) {
            return $notation;
        }

        return preg_replace('/\[([^\]]+)\]/', "['$1']", $notation);
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

    public function forEach(string $variable, string $value = 'value', ?string $key = null, Closure|string $content = ''): string
    {
        $value = ltrim($value, '$');
        $key = $key ? ltrim($key, '$') : null;

        $varPath = $this->buildVariablePath($variable);

        $asValue = $key !== null ? "{$key}|{$value}" : $value;

        if ($content instanceof Closure) {
            $inner = $this->executeWithContext($value, $content, isIteration: true);
        } else {
            $inner = trim($content);
        }

        $indented = $this->indentText($inner);

        $opening = "{{ foreach:{$varPath} as=\"{$asValue}\" }}";
        $closing = "{{ /foreach:{$varPath} }}";

        return $this->normalizeLineEndings("{$opening}\n{$indented}\n{$closing}");
    }

    public function html(string $name, bool $indent = false, bool $newLine = false): static
    {
        return $this->variable($name, selfClosing: true, indent: $indent, newLine: $newLine);
    }

    public function pairWithContext(string $name, Closure $content): string
    {
        $inner = $this->withIsolatedContext($name, $content, isIteration: false);
        $indented = $this->indentText($inner);

        return $this->normalizeLineEndings("{{ {$name} }}\n{$indented}\n{{ /{$name} }}");
    }

    protected function getConditionBuilder(): ConditionBuilder
    {
        return new ConditionBuilder(
            ifTemplate: "{{ if {condition} }}\n{template}\n",
            elseifTemplate: "{{ elseif {condition} }}\n{template}\n",
            endTemplate: '{{ /if }}',
        );
    }

    public function fields(array|Collection $fields): static
    {
        $this->optionalNewline();

        $this->content .= $this->generator()->scaffoldFields($fields);

        return $this;
    }
}
