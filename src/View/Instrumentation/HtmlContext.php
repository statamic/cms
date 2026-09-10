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

    /** @var string */
    public $kind;

    /** @var string|null */
    public $elementName;

    /** @var string|null */
    public $attributeName;

    /** @var string[] */
    public $elementStack = [];

    /** @var int|null */
    public $elementStartOffset;

    /** @var bool */
    public $isClosingTag;

    /** @var bool */
    public $requiresFormattingReconstruction;

    /** @var bool */
    public $documentHasExplicitStructure = false;

    /** @var bool */
    public $fosterParented = false;

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

    /** @return bool */
    public function isSafeForHtmlComments()
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

    public function isFosterParentingContext()
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

    public function isAttributeContext()
    {
        return in_array($this->kind, [self::KIND_ATTRIBUTE_NAME, self::KIND_ATTRIBUTE_VALUE], true);
    }

    public function isTagContext()
    {
        return in_array($this->kind, [
            self::KIND_TAG_OPEN,
            self::KIND_ELEMENT_NAME,
            self::KIND_ATTRIBUTE_NAME,
            self::KIND_ATTRIBUTE_VALUE,
        ], true);
    }

    public function isElementContent()
    {
        return $this->kind === self::KIND_ELEMENT_CONTENT;
    }

    public function canInstrumentOwningElement()
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

    public function canInstrumentContainingElement()
    {
        if (! $this->hasInstrumentableElement()) {
            return false;
        }

        return in_array($this->kind, [self::KIND_ELEMENT_CONTENT, self::KIND_RAW_TEXT], true);
    }

    protected function hasInstrumentableElement()
    {
        if ($this->isClosingTag) {
            return false;
        }

        if ($this->elementName === null || $this->elementStartOffset === null) {
            return false;
        }

        return ! in_array(self::DYNAMIC_ELEMENT, $this->elementStack, true);
    }

    public function canInstrumentElement()
    {
        return $this->canInstrumentOwningElement() || $this->canInstrumentContainingElement();
    }

    public function sharesElementContainerWith(HtmlContext $other)
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
