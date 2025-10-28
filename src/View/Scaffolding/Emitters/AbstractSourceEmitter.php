<?php

namespace Statamic\View\Scaffolding\Emitters;

use Closure;
use Illuminate\Support\Str;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Statamic\View\Scaffolding\TemplateGenerator;
use Stringable;

abstract class AbstractSourceEmitter implements Stringable
{
    protected static array $blueprintStack = [];

    protected array $specialLoopVariables = [
        'key',
        'value',
    ];

    protected string $content = '';

    protected int $indentSize = 4;

    protected string $lineEnding = PHP_EOL;

    protected string $indentChar = ' ';

    protected bool $finalNewline = false;

    protected bool $preferComponentSyntax = false;

    public function setPreferComponentSyntax(bool $preferComponentSyntax): static
    {
        $this->preferComponentSyntax = $preferComponentSyntax;

        return $this;
    }

    public function setNewline(string $newline): static
    {
        $this->lineEnding = match ($newline) {
            'LF' => "\n",
            'CRLF' => "\r\n",
            'CR' => "\r",
            default => PHP_EOL,
        };

        return $this;
    }

    public function setIndentType(string $char): static
    {
        $this->indentChar = match ($char) {
            'tab' => "\t",
            default => ' ',
        };

        return $this;
    }

    public function setIndentSize(int $size): static
    {
        $this->indentSize = $size;

        return $this;
    }

    public function setFinalNewline(bool $finalNewline): static
    {
        $this->finalNewline = $finalNewline;

        return $this;
    }

    public function getLineEnding(): string
    {
        return $this->lineEnding;
    }

    public function getIndentChar(): string
    {
        return $this->indentChar;
    }

    public function getIndentSize(): int
    {
        return $this->indentSize;
    }

    public function getFinalNewline(): bool
    {
        return $this->finalNewline;
    }

    public function getPreferComponentSyntax(): bool
    {
        return $this->preferComponentSyntax;
    }

    protected function createInstance(): static
    {
        $instance = new static();

        $instance->lineEnding = $this->lineEnding;
        $instance->indentChar = $this->indentChar;
        $instance->indentSize = $this->indentSize;
        $instance->finalNewline = $this->finalNewline;
        $instance->preferComponentSyntax = $this->preferComponentSyntax;

        return $instance;
    }

    protected function generator(): TemplateGenerator
    {
        $generator = app(TemplateGenerator::class)->withCoreGenerators();

        $language = str_contains(static::class, 'Blade') ? 'blade' : 'antlers';

        $generator
            ->templateLanguage($language)
            ->preferComponentSyntax($this->preferComponentSyntax)
            ->lineEnding($this->lineEnding)
            ->indentType($this->indentChar)
            ->indentSize($this->indentSize)
            ->finalNewline($this->finalNewline);

        return $generator;
    }

    protected function lineEndingToString(): string
    {
        return match ($this->lineEnding) {
            "\n" => 'LF',
            "\r\n" => 'CRLF',
            "\r" => 'CR',
            default => 'auto',
        };
    }

    abstract public function varName(string $variable): string;

    abstract protected function getConditionBuilder(): ConditionBuilder;

    public function makeLoopVariableName(string $handle, ?string $fallback = null)
    {
        if (in_array($handle, $this->specialLoopVariables)) {
            return $handle;
        }

        $var = str($handle)
            ->afterLast('->')
            ->afterLast('.')
            ->trim()
            ->value();

        $singular = Str::singular($var);

        if ($singular === $var) {
            if ($fallback) {
                return $fallback;
            }

            $singular = "{$handle}_item";
        }

        return Str::snake($singular);
    }

    public function append(string $text): static
    {
        $this->optionalNewline();
        $this->content .= $text;

        return $this;
    }

    public function newline(): static
    {
        $this->content .= "\n";

        return $this;
    }

    public function optionalNewline(): static
    {
        if (strlen($this->content) > 0) {
            $this->content .= "\n";
        }

        return $this;
    }

    public function indentText(string $text, ?int $spaces = null): string
    {
        $spaces = $spaces ?? $this->indentSize;
        $prefix = str_repeat($this->indentChar, $spaces);
        $lines = explode("\n", $text);
        $indented = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                $indented[] = '';
            } else {
                $indented[] = $prefix.$line;
            }
        }

        return implode("\n", $indented);
    }

    public function raw(string $template): string
    {
        $lines = explode("\n", $template);

        $minIndent = PHP_INT_MAX;
        $indentUnit = null;

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $indent = strlen($line) - strlen(ltrim($line));
            $minIndent = min($minIndent, $indent);

            if ($indent > 0 && $indentUnit === null) {
                $indentUnit = $indent;
            } elseif ($indent > 0 && $indentUnit !== null && $indent < $indentUnit) {
                $indentUnit = $indent;
            }
        }

        if ($minIndent === PHP_INT_MAX) {
            $minIndent = 0;
        }

        if ($indentUnit === null || $indentUnit === 0) {
            $indentUnit = $minIndent > 0 ? $minIndent : 2;
        }

        $processedLines = array_map(function ($line) use ($minIndent, $indentUnit) {
            if (trim($line) === '') {
                return '';
            }

            $currentIndent = strlen($line) - strlen(ltrim($line));
            $relativeIndent = $currentIndent - $minIndent;
            $content = $minIndent > 0 ? substr($line, $minIndent) : $line;

            if ($relativeIndent > 0) {
                $indentLevels = (int) round($relativeIndent / $indentUnit);
                $prefix = str_repeat($this->indentChar, $indentLevels * $this->indentSize);

                return $prefix.ltrim($content);
            }

            return ltrim($content);
        }, $lines);

        return $this->normalizeLineEndings(trim(implode("\n", $processedLines)));
    }

    public function condition(array $branches): static
    {
        $builder = $this->getConditionBuilder();
        $this->content .= $builder->build($branches, fn ($template) => $this->indentText($template));

        return $this;
    }

    public function component(string $name, Closure $content, array $params = []): string
    {
        $emit = $this->createInstance();

        $inner = trim($this->withStackCleanup(fn () => $content($emit)));

        $indented = $this->indentText($inner);

        $opening = "<s:{$name}";

        foreach ($params as $key => $value) {
            $opening .= " {$key}=\"{$value}\"";
        }

        $opening .= '>';

        return $this->normalizeLineEndings("{$opening}\n{$indented}\n</s:{$name}>");
    }

    protected function reset(): void
    {
        $this->content = '';
    }

    public function __toString(): string
    {
        $result = $this->content;
        $this->reset();

        $result = $this->normalizeLineEndings($result);

        if ($this->finalNewline && $result !== '' && ! str_ends_with($result, $this->lineEnding)) {
            $result .= $this->lineEnding;
        }

        return $result;
    }

    protected function normalizeLineEndings(string $text): string
    {
        return StringUtilities::normalizeLineEndings($text, $this->lineEnding);
    }

    protected function withStackCleanup(Closure $callback): string
    {
        $initialStackSize = count(static::$variableStack ?? []);

        try {
            return (string) $callback();
        } finally {
            if (isset(static::$variableStack)) {
                $finalStackSize = count(static::$variableStack);

                if ($finalStackSize > $initialStackSize) {
                    for ($i = $finalStackSize; $i > $initialStackSize; $i--) {
                        array_pop(static::$variableStack);
                    }

                    static::$currentIterationVar = null;
                    foreach (array_reverse(static::$variableStack) as $context) {
                        if ($context['iteration']) {
                            static::$currentIterationVar = $context['var'];
                            break;
                        }
                    }
                }
            }
        }
    }
}
