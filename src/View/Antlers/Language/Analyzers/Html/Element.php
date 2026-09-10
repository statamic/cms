<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html;

use Illuminate\Support\LazyCollection;
use Statamic\View\Html\Concerns\QueriesElementBasics;
use Statamic\View\Instrumentation\Antlers\RegionExtents;
use Statamic\View\Instrumentation\HtmlSpec;

/** @internal */
class Element extends Node
{
    use Container, QueriesElementBasics;

    protected $name;

    protected $namespace;

    protected $openingTag;

    protected $closingTag = '';

    protected $selfClosing = false;

    protected $synthetic = false;

    /** @var array<string, mixed> */
    protected $attributes = [];

    /** @var array<string, array<int, array{start: int, length: int}>> */
    protected $attributeSpans = [];

    /** @var array<string, mixed> */
    protected $changedAttributes = [];

    /** @var array<string, string> */
    protected $changedAttributeNames = [];

    /** @var array<string, bool> */
    protected $removedAttributes = [];

    protected $attributesParsed = false;

    public function __construct(Document $document, $name, $openingTag, $selfClosing = false, $namespace = 'html')
    {
        parent::__construct($document);

        $this->name = $name;
        $this->namespace = $namespace;
        $this->openingTag = $openingTag;
        $this->selfClosing = $selfClosing;
    }

    public static function fromArena(Document $document, $id)
    {
        $arena = $document->arena();
        $type = $arena->kind($id);
        $element = new self(
            $document,
            $arena->name[$id],
            $arena->value[$id],
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

    public function namespace()
    {
        return $this->namespace;
    }

    public function isHtml()
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
    public function ancestors()
    {
        return LazyCollection::make(function () {
            for ($element = $this->parentElement(); $element !== null; $element = $element->parentElement()) {
                yield $element;
            }
        });
    }

    /** @return LazyCollection<int, Element> */
    public function ancestorsAndSelf()
    {
        return LazyCollection::make(function () {
            for ($element = $this; $element !== null; $element = $element->parentElement()) {
                yield $element;
            }
        });
    }

    public function closingTag($tag = null)
    {
        if (func_num_args() === 0) {
            return $this->closingTag;
        }

        $this->closingTag = (string) $tag;

        if ($this->arenaId !== null) {
            $this->document->arena()->closing[$this->arenaId] = $this->closingTag;
        }

        return $this;
    }

    public function isSelfClosing()
    {
        return $this->selfClosing;
    }

    public function isSynthetic(): bool
    {
        return $this->synthetic;
    }

    public function releaseSourceAnchors()
    {
        parent::releaseSourceAnchors();

        foreach ($this->sourceChildren() as $child) {
            $child->releaseSourceAnchors();
        }
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
        $attributes = $this->attributes;

        foreach ($this->removedAttributes as $name => $_) {
            unset($attributes[$name]);
        }

        foreach ($this->changedAttributes as $name => $value) {
            $attributes[$name] = $value;
        }

        return $attributes;
    }

    public function setAttribute($name, $value = null)
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

    public function removeAttribute($name)
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

    public function addClass($class)
    {
        $value = (string) $this->attr('class');

        // Authored attributes contain HTML entities; setAttribute values are
        // already decoded. Keep Antlers expressions untouched in either case.
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

    public function embeddedNodes()
    {
        return array_map([$this->document, 'handle'], $this->document->arena()->embedded[$this->arenaId] ?? []);
    }

    public function siblingsFor(Node $node)
    {
        if (in_array($node->arenaId(), $this->document->arena()->embedded[$this->arenaId] ?? [], true)) {
            return $this->embeddedNodes();
        }

        return $this->children();
    }

    protected function render()
    {
        if ($this->synthetic) {
            return $this->renderChildren().$this->closingTag;
        }

        return $this->renderOpeningTag().$this->renderChildren().$this->closingTag;
    }

    protected function renderOpeningTag()
    {
        if (empty($this->changedAttributes) && empty($this->removedAttributes)) {
            return $this->openingTag;
        }

        $edits = [];

        foreach ($this->removedAttributes as $name => $_) {
            foreach ($this->attributeSpans[$name] ?? [] as $span) {
                $edits[] = [$span['start'], $span['length'], ''];
            }
        }

        $new = '';

        foreach ($this->changedAttributes as $name => $value) {
            $attribute = ' '.($this->changedAttributeNames[$name] ?? $name);

            if ($value !== null) {
                $attribute .= '="'.$this->escapeAttributeValue($value).'"';
            }

            if (isset($this->attributeSpans[$name])) {
                $spans = $this->attributeSpans[$name];
                $span = array_shift($spans);
                $edits[] = [$span['start'], $span['length'], $attribute];

                foreach ($spans as $duplicate) {
                    $edits[] = [$duplicate['start'], $duplicate['length'], ''];
                }
            } else {
                $new .= $attribute;
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

        return $opening;
    }

    protected function parseAttributes()
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

            if (substr($this->openingTag, $index, 2) === '{{') {
                $index = $this->antlersEnd($index);

                continue;
            }

            $attributeStart = $index;
            $dynamicName = false;

            if ($this->openingTag[$index] === '=') {
                $index++;
            }

            while ($index < $length
                && ! HtmlSpec::isAttributeNameDelimiter($this->openingTag[$index])) {
                if (substr($this->openingTag, $index, 2) === '{{') {
                    $dynamicName = true;
                    $index = $this->antlersEnd($index);

                    continue;
                }

                $index++;
            }

            $name = strtolower(str_replace(
                "\0",
                "\xEF\xBF\xBD",
                substr($this->openingTag, $attributeStart, $index - $attributeStart)
            ));

            if ($name === '') {
                $index++;

                continue;
            }

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
                        if (substr($this->openingTag, $index, 2) === '{{') {
                            $index = $this->antlersEnd($index);

                            continue;
                        }

                        $index++;
                    }

                    $value = str_replace(
                        "\0",
                        "\xEF\xBF\xBD",
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
                        if (substr($this->openingTag, $index, 2) === '{{') {
                            $index = $this->antlersEnd($index);

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
            }

            $this->attributeSpans[$name][] = [
                'start' => $spanStart,
                'length' => $attributeEnd - $spanStart,
            ];
        }
    }

    protected function isHtmlSpace($char)
    {
        return HtmlSpec::isHtmlSpace($char);
    }

    protected function ensureAttributesParsed()
    {
        if (! $this->attributesParsed) {
            $this->attributesParsed = true;
            $this->parseAttributes();
        }
    }

    protected function openingNameEnd()
    {
        $length = strlen($this->openingTag);

        for ($index = 1; $index < $length; $index++) {
            if (substr($this->openingTag, $index, 2) === '{{') {
                $index = $this->antlersEnd($index) - 1;

                continue;
            }

            $char = $this->openingTag[$index];

            if (HtmlSpec::isTagNameDelimiter($char)) {
                return $index;
            }
        }

        return $length;
    }

    protected function antlersEnd($start)
    {
        return $this->antlersEndIn($this->openingTag, $start);
    }

    protected function escapeAttributeValue($value)
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

    protected function antlersEndIn($source, $start)
    {
        $length = strlen($source);
        $end = RegionExtents::endInBytes($source, $start, $length);

        if ($end === false) {
            return $length;
        }

        return $end;
    }

    protected function markDirty()
    {
        $this->touchDocument();
    }
}
