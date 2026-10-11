<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html;

use Illuminate\Support\LazyCollection;
use Statamic\View\Instrumentation\Antlers\RegionExtents;
use Statamic\View\Instrumentation\HtmlSpec;

/** @internal */
class Element extends Node
{
    use Container;

    protected ?string $name;

    protected string $namespace;

    protected string $openingTag;

    protected string $closingTag = '';

    protected bool $selfClosing = false;

    protected bool $synthetic = false;

    /** @var array<string, mixed> */
    protected array $attributes = [];

    /** @var array<string, string> */
    protected array $attributeNames = [];

    /** @var array<string, array<int, array{start: int, length: int, whitespace: int, trailing: int}>> */
    protected array $attributeSpans = [];

    /** @var array<string, mixed> */
    protected array $changedAttributes = [];

    /** @var array<string, string> */
    protected array $changedAttributeNames = [];

    /** @var array<string, bool> */
    protected array $removedAttributes = [];

    protected bool $attributesParsed = false;

    public function __construct(Document $document, ?string $name, string $openingTag, bool $selfClosing = false, string $namespace = 'html')
    {
        parent::__construct($document);

        $this->name = $name;
        $this->namespace = $namespace;
        $this->openingTag = $openingTag;
        $this->selfClosing = $selfClosing;
    }

    public static function fromArena(Document $document, int $id): self
    {
        $arena = $document->arena();
        $type = $arena->kind($id);
        $element = new self(
            $document,
            $arena->name[$id],
            $document->valueMarkup($id),
            (bool) ($type & Arena::SELF_CLOSING),
            $arena->namespace($id)
        );
        $element->arenaId = $id;
        $element->closingTag = $arena->closing[$id];
        $element->synthetic = (bool) ($type & Arena::SYNTHETIC);

        return $element;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function namespace(): string
    {
        return $this->namespace;
    }

    public function isHtml(): bool
    {
        return $this->namespace === 'html';
    }

    public function sourceLine(): ?int
    {
        if ($this->arenaId === null) {
            return null;
        }

        return $this->document->elementSourceLine($this->arenaId);
    }

    public function sourceOffset(): ?int
    {
        if ($this->arenaId === null) {
            return null;
        }

        return $this->document->elementSourceCharacterOffset($this->arenaId);
    }

    /** @return LazyCollection<int, Element> */
    public function ancestors(): LazyCollection
    {
        return LazyCollection::make(function () {
            for ($element = $this->parentElement(); $element !== null; $element = $element->parentElement()) {
                yield $element;
            }
        });
    }

    /** @return LazyCollection<int, Element> */
    public function ancestorsAndSelf(): LazyCollection
    {
        return LazyCollection::make(function () {
            for ($element = $this; $element !== null; $element = $element->parentElement()) {
                yield $element;
            }
        });
    }

    public function closingTag(): string
    {
        if ($this->arenaId !== null) {
            return $this->document->closingMarkup($this->arenaId);
        }

        return $this->closingTag;
    }

    public function setClosingTag(string $tag): static
    {
        if (! $this->acceptsClosingTag()) {
            throw new \LogicException('Reconstructed, void and self-closing HTML elements cannot have a closing tag.');
        }

        $this->closingTag = $tag;

        if ($this->arenaId !== null) {
            $this->document->arena()->closing[$this->arenaId] = $tag;
            unset($this->document->arena()->closingStart[$this->arenaId]);
        }

        $this->touchDocument();

        return $this;
    }

    protected function acceptsClosingTag(): bool
    {
        if ($this->synthetic) {
            return false;
        }

        if (! $this->isHtml()) {
            return ! $this->selfClosing;
        }

        if (Document::isVoidElement($this->name)) {
            return false;
        }

        if (! $this->selfClosing) {
            return true;
        }

        return $this->name !== null && ! $this->document->isComponentElementName($this->name);
    }

    public function isSelfClosing(): bool
    {
        return $this->selfClosing;
    }

    public function isSynthetic(): bool
    {
        return $this->synthetic;
    }

    public function is(string $name): bool
    {
        return $this->name !== null && strcasecmp($this->name, $name) === 0;
    }

    /** @return string[] */
    public function classes(): array
    {
        return HtmlSpec::classList((string) $this->attr('class'));
    }

    public function hasClass(string $class): bool
    {
        return in_array($class, $this->classes(), true);
    }

    public function attr(string $name): ?string
    {
        $this->ensureAttributesParsed();
        $key = strtolower($name);

        if (isset($this->removedAttributes[$key])) {
            return null;
        }

        if (array_key_exists($key, $this->changedAttributes)) {
            return $this->changedAttributes[$key];
        }

        return $this->attributes[$key] ?? null;
    }

    public function hasAttribute(string $name): bool
    {
        $this->ensureAttributesParsed();
        $key = strtolower($name);

        if (isset($this->removedAttributes[$key])) {
            return false;
        }

        if (array_key_exists($key, $this->changedAttributes)) {
            return true;
        }

        return array_key_exists($key, $this->attributes);
    }

    public function attributes(): array
    {
        $this->ensureAttributesParsed();
        $attributes = [];

        foreach ($this->attributes as $key => $value) {
            if (isset($this->removedAttributes[$key]) || array_key_exists($key, $this->changedAttributes)) {
                continue;
            }

            $attributes[$this->attributeNames[$key] ?? $key] = $value;
        }

        foreach ($this->changedAttributes as $key => $value) {
            $attributes[$this->changedAttributeNames[$key] ?? $key] = $value;
        }

        return $attributes;
    }

    public function setAttribute(string $name, ?string $value = null): static
    {
        if ($this->synthetic) {
            throw new \LogicException('Reconstructed HTML elements have no source opening tag to mutate.');
        }

        if (! HtmlSpec::isValidName($name)) {
            throw new \InvalidArgumentException('Invalid HTML attribute name.');
        }

        $this->ensureAttributesParsed();
        $key = strtolower($name);
        unset($this->removedAttributes[$key]);

        if ($value !== null) {
            $value = (string) $value;
        }

        $attributeName = $name;

        if ($this->isHtml()) {
            $attributeName = $key;
        }

        $this->changedAttributes[$key] = $value;
        $this->changedAttributeNames[$key] = $attributeName;
        $this->touchDocument();

        return $this;
    }

    public function removeAttribute(string $name): static
    {
        if ($this->synthetic) {
            throw new \LogicException('Reconstructed HTML elements have no source opening tag to mutate.');
        }

        $this->ensureAttributesParsed();
        $key = strtolower($name);
        unset($this->changedAttributes[$key]);
        unset($this->changedAttributeNames[$key]);
        $this->removedAttributes[$key] = true;
        $this->touchDocument();

        return $this;
    }

    public function addClass(string $class): static
    {
        $value = (string) $this->attr('class');

        if (! array_key_exists('class', $this->changedAttributes)) {
            $value = $this->decodeAttributeValue($value);
        }

        $classes = HtmlSpec::classList($value);

        foreach (preg_split('/\s+/', trim((string) $class)) as $candidate) {
            if ($candidate !== '' && ! in_array($candidate, $classes, true)) {
                $classes[] = $candidate;
            }
        }

        return $this->setAttribute('class', implode(' ', $classes));
    }

    protected function render(): string
    {
        if ($this->synthetic) {
            return $this->renderChildren().$this->closingTag();
        }

        return $this->renderOpeningTag().$this->renderChildren().$this->closingTag();
    }

    protected function renderOpeningTag(): string
    {
        if (empty($this->changedAttributes) && empty($this->removedAttributes)) {
            return $this->openingTag;
        }

        $edits = [];

        foreach ($this->removedAttributes as $name => $_) {
            foreach ($this->attributeSpans[$name] ?? [] as $span) {
                $edits[] = [$span['start'], $span['length'] + $span['trailing'], ''];
            }
        }

        $new = '';

        foreach ($this->changedAttributes as $name => $value) {
            $attribute = $this->changedAttributeNames[$name] ?? $name;

            if ($value !== null) {
                $attribute .= '="'.$this->escapeAttributeValue($value).'"';
            }

            if (isset($this->attributeSpans[$name])) {
                $spans = $this->attributeSpans[$name];
                $span = array_shift($spans);
                $whitespace = substr($this->openingTag, $span['start'], $span['whitespace']);
                $edits[] = [$span['start'], $span['length'], $whitespace.$attribute];

                foreach ($spans as $duplicate) {
                    $edits[] = [$duplicate['start'], $duplicate['length'] + $duplicate['trailing'], ''];
                }
            } else {
                $new .= ' '.$attribute;
            }
        }

        usort($edits, function ($left, $right) {
            return $right[0] <=> $left[0];
        });

        $opening = $this->openingTag;

        foreach ($edits as [$start, $length, $replacement]) {
            $opening = substr($opening, 0, $start).$replacement.substr($opening, $start + $length);
        }

        if ($new !== '') {
            $offset = $this->openingNameEnd();
            $opening = substr($opening, 0, $offset).$new.substr($opening, $offset);
        }

        if (str_starts_with($opening, '</')) {
            $opening = '<'.substr($opening, 2);
        }

        return $this->document->adoptLineEndings($opening);
    }

    protected function parseAttributes(): void
    {
        $length = strlen($this->openingTag);
        $index = $this->openingNameEnd();

        while ($index < $length) {
            while ($index < $length && $this->isHtmlSpace($this->openingTag[$index])) {
                $index++;
            }

            if ($index >= $length || $this->openingTag[$index] === '>') {
                break;
            }

            if ($this->openingTag[$index] === '/') {
                $index++;

                continue;
            }

            if (($embeddedEnd = $this->embeddedEnd($index)) !== null) {
                $index = $embeddedEnd;

                continue;
            }

            $attributeStart = $index;
            $dynamicName = false;

            if ($this->openingTag[$index] === '=') {
                $index++;
            }

            while ($index < $length
                && ! HtmlSpec::isAttributeNameDelimiter($this->openingTag[$index])) {
                if (($embeddedEnd = $this->embeddedEnd($index)) !== null) {
                    $dynamicName = true;
                    $index = $embeddedEnd;

                    continue;
                }

                $index++;
            }

            $rawName = str_replace(
                "\0",
                "\xEF\xBF\xBD",
                substr($this->openingTag, $attributeStart, $index - $attributeStart)
            );
            $name = strtolower($rawName);

            $attributeEnd = $index;

            while ($index < $length && $this->isHtmlSpace($this->openingTag[$index])) {
                $index++;
            }

            $value = null;

            if ($index < $length && $this->openingTag[$index] === '=') {
                $index++;

                while ($index < $length && $this->isHtmlSpace($this->openingTag[$index])) {
                    $index++;
                }

                if ($index < $length && ($this->openingTag[$index] === '"' || $this->openingTag[$index] === "'")) {
                    $quote = $this->openingTag[$index++];
                    $valueStart = $index;

                    while ($index < $length && $this->openingTag[$index] !== $quote) {
                        if (($embeddedEnd = $this->embeddedEnd($index)) !== null) {
                            $index = $embeddedEnd;

                            continue;
                        }

                        $index++;
                    }

                    $value = str_replace(
                        ["\0", "\r\n", "\r"],
                        ["\xEF\xBF\xBD", "\n", "\n"],
                        substr($this->openingTag, $valueStart, $index - $valueStart)
                    );
                    if ($index < $length) {
                        $index++;
                    }
                } else {
                    $valueStart = $index;

                    while ($index < $length
                        && ! $this->isHtmlSpace($this->openingTag[$index])
                        && $this->openingTag[$index] !== '>') {
                        if (($embeddedEnd = $this->embeddedEnd($index)) !== null) {
                            $index = $embeddedEnd;

                            continue;
                        }

                        $index++;
                    }

                    $value = str_replace(
                        "\0",
                        "\xEF\xBF\xBD",
                        substr($this->openingTag, $valueStart, $index - $valueStart)
                    );
                }

                $attributeEnd = $index;
            }

            $spanStart = $attributeStart;

            while ($spanStart > 0 && $this->isHtmlSpace($this->openingTag[$spanStart - 1])) {
                $spanStart--;
            }

            if ($dynamicName) {
                continue;
            }

            if (! array_key_exists($name, $this->attributes)) {
                $this->attributes[$name] = $value;
                $this->attributeNames[$name] = $this->isHtml() ? $name : $rawName;
            }

            $trailingEnd = $attributeEnd;

            while ($trailingEnd < $length && $this->isHtmlSpace($this->openingTag[$trailingEnd])) {
                $trailingEnd++;
            }

            if ($trailingEnd < $length && $this->openingTag[$trailingEnd] !== '>') {
                $trailingEnd = $attributeEnd;
            }

            $this->attributeSpans[$name][] = [
                'start' => $spanStart,
                'length' => $attributeEnd - $spanStart,
                'whitespace' => $attributeStart - $spanStart,
                'trailing' => $trailingEnd - $attributeEnd,
            ];
        }
    }

    protected function isHtmlSpace(string $char): bool
    {
        return HtmlSpec::isHtmlSpace($char);
    }

    protected function ensureAttributesParsed(): void
    {
        if (! $this->attributesParsed) {
            $this->attributesParsed = true;
            $this->parseAttributes();
        }
    }

    protected function openingNameEnd(): int
    {
        $length = strlen($this->openingTag);
        $index = str_starts_with($this->openingTag, '</') ? 2 : 1;

        for (; $index < $length; $index++) {
            if (($embeddedEnd = $this->embeddedEnd($index)) !== null) {
                $index = $embeddedEnd - 1;

                continue;
            }

            $char = $this->openingTag[$index];

            if (HtmlSpec::isTagNameDelimiter($char)) {
                return $index;
            }
        }

        return $length;
    }

    protected function embeddedEnd(int $start): ?int
    {
        $length = strlen($this->openingTag);
        $end = RegionExtents::embeddedEndInBytes($this->openingTag, $start, $length);

        if ($end === false) {
            return $length;
        }

        return $end;
    }

    protected function escapeAttributeValue(string $value): string
    {
        return $this->transformAttributeLiterals(
            (string) $value,
            fn ($literal) => htmlspecialchars($literal, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );
    }

    protected function decodeAttributeValue(string $value): string
    {
        return $this->transformAttributeLiterals(
            $value,
            fn ($literal) => html_entity_decode($literal, ENT_QUOTES | ENT_HTML5, 'UTF-8')
        );
    }

    protected function transformAttributeLiterals(string $value, callable $transform): string
    {
        $result = '';
        $offset = 0;
        $length = strlen($value);

        while (($start = strpos($value, '{{', $offset)) !== false) {
            $end = RegionExtents::endInBytes($value, $start, $length);

            if ($end === false) {
                break;
            }

            $result .= $transform(substr($value, $offset, $start - $offset));
            $region = substr($value, $start, $end - $start);
            $result .= $start > 0 && $value[$start - 1] === '@'
                ? $transform($region)
                : $region;
            $offset = $end;
        }

        return $result.$transform(substr($value, $offset));
    }

    protected function antlersEndIn(string $source, int $start): int
    {
        $length = strlen($source);
        $end = RegionExtents::endInBytes($source, $start, $length);

        if ($end === false) {
            return $length;
        }

        return $end;
    }

    protected function markDirty(): void
    {
        $this->touchDocument();
    }
}
