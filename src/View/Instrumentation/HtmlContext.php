<?php

namespace Statamic\View\Instrumentation;

/** @internal */
class HtmlContext
{
    const DYNAMIC_ELEMENT = '*';

    const KIND_ELEMENT_CONTENT = 'element-content';

    const KIND_TAG_OPEN = 'tag-open';

    const KIND_ELEMENT_NAME = 'element-name';

    const KIND_ATTRIBUTE_NAME = 'attribute-name';

    const KIND_ATTRIBUTE_VALUE = 'attribute-value';

    const KIND_RAW_TEXT = 'raw-text';

    const KIND_COMMENT = 'comment';

    const KIND_DOCTYPE = 'doctype';

    const KIND_PHP = 'php';

    public string $kind;

    public ?string $elementName;

    public ?string $attributeName;

    /** @var string[] */
    public array $elementStack = [];

    public ?int $elementStartOffset;

    public bool $isClosingTag;

    public bool $requiresFormattingReconstruction;

    public bool $documentHasExplicitStructure = false;

    public bool $fosterParented = false;

    public function __construct(
        string $kind,
        ?string $elementName = null,
        ?string $attributeName = null,
        array $elementStack = [],
        ?int $elementStartOffset = null,
        bool $isClosingTag = false,
        bool $requiresFormattingReconstruction = false
    ) {
        $this->kind = $kind;
        $this->elementName = $elementName;
        $this->attributeName = $attributeName;
        $this->elementStack = $elementStack;
        $this->elementStartOffset = $elementStartOffset;
        $this->isClosingTag = $isClosingTag;
        $this->requiresFormattingReconstruction = $requiresFormattingReconstruction;
    }

    /** @return array{0: string, 1: string|null, 2: string|null, 3: string[], 4: int|null, 5: int} */
    public function toArray(): array
    {
        return [
            $this->kind,
            $this->elementName,
            $this->attributeName,
            $this->elementStack,
            $this->elementStartOffset,
            ($this->isClosingTag ? 1 : 0)
            | ($this->requiresFormattingReconstruction ? 2 : 0)
            | ($this->documentHasExplicitStructure ? 4 : 0)
            | ($this->fosterParented ? 8 : 0),
        ];
    }

    /** @param  array{0: string, 1: string|null, 2: string|null, 3: string[], 4: int|null, 5: int}  $data */
    public static function fromArray(array $data): static
    {
        $context = new static(
            $data[0],
            $data[1],
            $data[2],
            $data[3],
            $data[4],
            ($data[5] & 1) === 1,
            ($data[5] & 2) === 2
        );
        $context->documentHasExplicitStructure = ($data[5] & 4) === 4;
        $context->fosterParented = ($data[5] & 8) === 8;

        return $context;
    }

    public function isSafeForHtmlComments(): bool
    {
        if ($this->kind !== self::KIND_ELEMENT_CONTENT) {
            return false;
        }

        if ($this->requiresFormattingReconstruction) {
            return false;
        }

        if ($this->documentHasExplicitStructure
            && ($this->elementStack === [] || $this->elementStack === ['html'])) {
            return false;
        }

        if (in_array(self::DYNAMIC_ELEMENT, $this->elementStack, true)) {
            return false;
        }

        if ($this->isFosterParentingContext()) {
            return false;
        }

        foreach ($this->elementStack as $name) {
            if (isset(HtmlSpec::COMMENT_UNSAFE_ELEMENTS[$name])) {
                return false;
            }
        }

        return true;
    }

    public function isFosterParentingContext(): bool
    {
        if ($this->fosterParented) {
            return true;
        }

        $table = null;

        for ($index = count($this->elementStack) - 1; $index >= 0; $index--) {
            if ($this->elementStack[$index] === 'table') {
                $table = $index;

                break;
            }
        }

        if ($table === null) {
            return false;
        }

        $tableModes = [
            'thead' => true,
            'tbody' => true,
            'tfoot' => true,
            'tr' => true,
            'colgroup' => true,
        ];

        for ($index = $table + 1, $count = count($this->elementStack); $index < $count; $index++) {
            $name = $this->elementStack[$index];

            if ($name === 'td' || $name === 'th' || $name === 'caption') {
                return false;
            }

            if (! isset($tableModes[$name])) {
                return false;
            }
        }

        return true;
    }

    public function canInstrumentOwningElement(): bool
    {
        if (! $this->hasInstrumentableElement()) {
            return false;
        }

        return in_array($this->kind, [
            self::KIND_TAG_OPEN,
            self::KIND_ATTRIBUTE_NAME,
            self::KIND_ATTRIBUTE_VALUE,
        ], true);
    }

    public function canInstrumentContainingElement(): bool
    {
        if (! $this->hasInstrumentableElement()) {
            return false;
        }

        return in_array($this->kind, [self::KIND_ELEMENT_CONTENT, self::KIND_RAW_TEXT], true);
    }

    protected function hasInstrumentableElement(): bool
    {
        if ($this->isClosingTag) {
            return false;
        }

        if ($this->elementName === null || $this->elementStartOffset === null) {
            return false;
        }

        return ! in_array(self::DYNAMIC_ELEMENT, $this->elementStack, true);
    }

    public function canInstrumentElement(): bool
    {
        return $this->canInstrumentOwningElement() || $this->canInstrumentContainingElement();
    }

    public function sharesElementContainerWith(HtmlContext $other): bool
    {
        if ($this->elementStack !== $other->elementStack) {
            return false;
        }

        if ($this->elementStartOffset !== $other->elementStartOffset) {
            return false;
        }

        if ($this->requiresFormattingReconstruction || $other->requiresFormattingReconstruction) {
            return false;
        }

        return ! in_array(self::DYNAMIC_ELEMENT, $this->elementStack, true);
    }
}
