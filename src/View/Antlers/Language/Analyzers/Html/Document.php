<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html;

use Illuminate\Support\LazyCollection;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Tappable;
use Statamic\View\Antlers\Language\Analyzers\NodeTypeAnalyzer;
use Statamic\View\Antlers\Language\Nodes\AntlersNode as LanguageAntlersNode;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Runtime\EnvironmentDetails;
use Statamic\View\Antlers\Language\Utilities\CharacterOffsets;
use Statamic\View\Html\ElementCollection;
use Statamic\View\Instrumentation\HtmlSpec;

/** @internal */
class Document
{
    use Concerns\AdoptsFormattingElements,
        Concerns\AnchorsSourceNodes,
        Concerns\BuildsDocumentGraph,
        Concerns\ClosesImpliedElements,
        Concerns\ParsesForeignContent,
        Concerns\RecoversTables,
        Concerns\ScansMarkup,
        Conditionable,
        Container,
        Tappable;

    protected static $voidElements = HtmlSpec::VOID_ELEMENTS;

    protected static $rawTextElements = HtmlSpec::RAW_TEXT_ELEMENTS;

    protected static $pClosingElements = HtmlSpec::P_CLOSING_ELEMENTS;

    protected static $impliedCloseTargets = HtmlSpec::IMPLIED_CLOSE_TARGETS;

    protected static $scopeBoundaries = HtmlSpec::SCOPE_BOUNDARIES;

    protected static $listItemScopeBoundaries = HtmlSpec::LIST_ITEM_SCOPE_BOUNDARIES;

    protected static $buttonScopeBoundaries = HtmlSpec::BUTTON_SCOPE_BOUNDARIES;

    protected static $tableScopeBoundaries = HtmlSpec::TABLE_SCOPE_BOUNDARIES;

    protected static $tableScopedStarts = HtmlSpec::TABLE_SCOPED_STARTS;

    protected static $tableStructureStarts = HtmlSpec::TABLE_STRUCTURE_STARTS;

    protected static $headingElements = HtmlSpec::HEADING_ELEMENTS;

    protected static $scopedEndTags = HtmlSpec::SCOPED_END_TAGS;

    protected static $tableSections = HtmlSpec::TABLE_SECTIONS;

    protected static $tableStructuralElements = HtmlSpec::TABLE_STRUCTURAL_ELEMENTS;

    protected static $allowedInTable = HtmlSpec::ALLOWED_IN_TABLE;

    protected static $selectTableElements = HtmlSpec::SELECT_TABLE_ELEMENTS;

    protected static $svgHtmlIntegrationPoints = HtmlSpec::SVG_HTML_INTEGRATION_POINTS;

    protected static $mathMlTextIntegrationPoints = HtmlSpec::MATHML_TEXT_INTEGRATION_POINTS;

    protected static $foreignBreakoutElements = HtmlSpec::FOREIGN_BREAKOUT_ELEMENTS;

    protected static $formattingElements = HtmlSpec::FORMATTING_ELEMENTS;

    protected static $formattingMarkerElements = HtmlSpec::FORMATTING_MARKER_ELEMENTS;

    protected static $formattingBreakStarts = HtmlSpec::FORMATTING_BREAK_STARTS;

    protected static $svgTagNames = HtmlSpec::SVG_TAG_NAMES;

    /** @var Document */
    protected $document;

    /** @var Arena */
    protected $arena;

    protected $source;

    protected $dirty = false;

    /** @var array<int, int> */
    protected $antlersNodes = [];

    /** @var array<int, Node> */
    protected $handles = [];

    /** @var array<int, int> */
    protected $regionEnds = [];

    /** @var array<int, LanguageAntlersNode> */
    protected $regionNodes = [];

    /** @var int[] */
    protected $regionStarts = [];

    protected $regionCursor = 0;

    /** @var int[] */
    protected $stack = [];

    /** @var array<int, int|null> */
    protected $activeFormatting = [];

    protected $scriptEscape = self::SCRIPT_DATA;

    /** @var int|null */
    protected $openSelect;

    protected $selectInTable = false;

    /** @var int|null */
    protected $formElement;

    /** @var int|null */
    protected $textElement;

    protected $hasSeenTable = false;

    /** @var array<int, int> */
    protected $fosterCharacterTails = [];

    /** @var int */
    protected $tableStartDepth = 0;

    /** @var array<int, TableMode> template element id => current template insertion mode */
    protected $templateInsertionModes = [];

    /** @var array<int, array<string, mixed>> keyed by spl_object_id of the language node */
    protected $regionPlacements = [];

    /** @var array<int, int> element id => byte offset of its opening `<` */
    protected $elementSourceStarts = [];

    /** @var string */
    protected $authoredSource;

    /** @var array<int, array{offset: int, line: int}>|null keyed by normalized byte offset */
    protected $authoredPositions = null;

    protected array $componentPrefixes = [];

    private function __construct($source, $authoredSource = null)
    {
        $this->document = $this;
        $this->arena = new Arena();
        $this->source = $source;
        $this->authoredSource = $authoredSource ?? $source;
    }

    public function arena()
    {
        return $this->arena;
    }

    /** @return array<int, array<string, mixed>> */
    public function regionPlacements()
    {
        return $this->regionPlacements;
    }

    /** @return int|null */
    public function elementSourceStart($id)
    {
        if ($id === null) {
            return null;
        }

        return $this->elementSourceStarts[$id] ?? null;
    }

    /** @return int|null */
    public function elementSourceCharacterOffset($id)
    {
        return $this->authoredElementPosition($id)['offset'] ?? null;
    }

    /** @return int|null */
    public function elementSourceLine($id)
    {
        return $this->authoredElementPosition($id)['line'] ?? null;
    }

    /** @return array{offset: int, line: int}|null */
    protected function authoredElementPosition($id)
    {
        $byte = $this->elementSourceStart($id);

        if ($byte === null) {
            return null;
        }

        if ($this->authoredPositions === null) {
            $this->resolveAuthoredPositions();
        }

        return $this->authoredPositions[$byte] ?? null;
    }

    protected function resolveAuthoredPositions()
    {
        $this->authoredPositions = [];
        $bytes = array_values(array_unique($this->elementSourceStarts));

        if ($bytes === []) {
            return;
        }

        $normalizedCharacters = CharacterOffsets::toCharacters($this->source, $bytes);
        [$authoredBytes, $authoredCharacters] = CharacterOffsets::normalizedToBytesAndCharacters(
            $this->authoredSource,
            array_values($normalizedCharacters)
        );

        usort($bytes, fn ($left, $right) => $authoredBytes[$normalizedCharacters[$left]] <=> $authoredBytes[$normalizedCharacters[$right]]);

        $line = 1;
        $previousByte = 0;

        foreach ($bytes as $byte) {
            $authoredByte = $authoredBytes[$normalizedCharacters[$byte]];

            if ($authoredByte > $previousByte) {
                preg_match_all(
                    '/\r\n|\r|\n/',
                    substr($this->authoredSource, $previousByte, $authoredByte - $previousByte),
                    $lineEndings
                );
                $line += count($lineEndings[0]);
                $previousByte = $authoredByte;
            }

            $this->authoredPositions[$byte] = [
                'offset' => $authoredCharacters[$authoredByte],
                'line' => $line,
            ];
        }
    }

    protected function recordPlacement(LanguageAntlersNode $node, array $facts)
    {
        $identity = spl_object_id($node);

        if (! isset($this->regionPlacements[$identity])) {
            $this->regionPlacements[$identity] = $facts;
        }
    }

    /** @return array{0: array<int, string|null>, 1: int|null} */
    protected function insertionChain($id)
    {
        $chain = [];
        $innermost = null;

        for (; $id !== null && $id !== 0; $id = $this->arena->parent($id)) {
            if (($this->arena->kind($id) & Arena::KIND_MASK) !== Arena::ELEMENT) {
                continue;
            }

            if ($innermost === null && isset($this->elementSourceStarts[$id])) {
                $innermost = $id;
            }

            $chain[] = $this->arena->name[$id];
        }

        return [array_reverse($chain), $innermost];
    }

    public function handle($id)
    {
        if ($id === 0) {
            return $this;
        }

        if (isset($this->handles[$id])) {
            return $this->handles[$id];
        }

        switch ($this->arena->kind($id) & Arena::KIND_MASK) {
            case Arena::ELEMENT:
                $handle = Element::fromArena($this, $id);
                break;
            case Arena::TEXT:
                $handle = Text::fromArena($this, $id);
                break;
            case Arena::ANTLERS:
                $handle = AntlersNode::fromArena($this, $id);
                break;
            case Arena::OPAQUE:
                $handle = OpaqueNode::fromArena($this, $id);
                break;
            case Arena::SOURCE_ANCHOR:
                $handle = SourceAnchor::fromArena($this, $id);
                break;
            default:
                throw new \LogicException('Unknown HTML arena node kind.');
        }

        return $this->handles[$id] = $handle;
    }

    public static function fromParser(DocumentParser $parser, array $componentPrefixes = [])
    {
        $document = new self($parser->getParsedContent(), $parser->getOriginalContent());
        $document->componentPrefixes = $componentPrefixes;
        $document->build($parser->getNodes());

        return $document;
    }

    public static function parse($template)
    {
        if (NodeTypeAnalyzer::$environmentDetails === null) {
            NodeTypeAnalyzer::$environmentDetails = app(EnvironmentDetails::class);
        }

        $parser = new DocumentParser();
        $parser->parse($template);

        return $parser->html();
    }

    /**
     * @internal
     *
     * @param  array<int, array{0: int, 1: int}>  $ranges  Non-overlapping byte ranges.
     * @return array<int, bool> Whether a region is placed in reconstructed formatting.
     */
    public static function formattingRecoveryForRegions(string $source, array $ranges, array $componentPrefixes = []): array
    {
        $document = new self($source);
        $document->componentPrefixes = $componentPrefixes;

        foreach ($ranges as [$start, $end]) {
            $document->regionEnds[$start] = $end;
            $document->regionNodes[$start] = new LanguageAntlersNode;
        }

        ksort($document->regionEnds, SORT_NUMERIC);
        $document->regionStarts = array_keys($document->regionEnds);
        $document->build(null);
        $recovery = [];

        foreach ($document->regionNodes as $start => $node) {
            $placement = $document->regionPlacements[spl_object_id($node)] ?? [];
            $recovered = $placement['reconstructed'] ?? false;
            $id = $document->antlersNodes[spl_object_id($node)] ?? null;

            for ($parent = $id; $parent !== null; $parent = $document->arena->parent($parent)) {
                $recovered = $recovered || ((bool) ($document->arena->kind($parent) & Arena::SYNTHETIC)
                    && isset(HtmlSpec::FORMATTING_ELEMENTS[$document->arena->name[$parent] ?? '']));
            }

            $recovery[$start] = $recovered;
        }

        return $recovery;
    }

    public static function isVoidElement($name)
    {
        return is_string($name) && isset(self::$voidElements[strtolower($name)]);
    }

    public function createArenaElement($name)
    {
        if (! HtmlSpec::isValidName($name)) {
            throw new \InvalidArgumentException('Invalid HTML element name.');
        }

        $id = $this->arena->element($name, '<'.$name.'>');

        if (! self::isVoidElement($name)) {
            $this->arena->closing[$id] = '</'.$name.'>';
        }

        return $this->handle($id);
    }

    public function parent()
    {
        return null;
    }

    public function node(LanguageAntlersNode $node)
    {
        $id = $this->antlersNodes[spl_object_id($node)] ?? null;

        if ($id === null) {
            return null;
        }

        return $this->handle($id);
    }

    /** @return ElementCollection<int, Element> */
    public function elements(string ...$names): ElementCollection
    {
        return $this->arenaDescendants($names);
    }

    /** @return LazyCollection<int, Region> */
    public function regions($name = null)
    {
        return new LazyCollection(function () use ($name) {
            foreach ($this->regionStarts as $start) {
                $node = $this->regionNodes[$start];

                if (! $this->isRegionOpening($node, $name)) {
                    continue;
                }

                $opening = $this->node($node);
                $closing = $this->node($node->isClosedBy);

                if ($opening instanceof AntlersNode && $closing instanceof AntlersNode) {
                    yield new Region($opening, $closing);
                }
            }
        });
    }

    protected function isRegionOpening($node, $name)
    {
        if (! $node instanceof LanguageAntlersNode || $node->isClosingTag) {
            return false;
        }

        if (! $node->isClosedBy instanceof LanguageAntlersNode) {
            return false;
        }

        if ($name === null) {
            return true;
        }

        return $node->name !== null && strcasecmp($node->name->compound, $name) === 0;
    }

    public function first(?string $name = null): ?Element
    {
        if ($name === null) {
            return $this->arenaFirstDescendant([]);
        }

        return $this->arenaFirstDescendant([$name]);
    }

    public function isDirty()
    {
        return $this->dirty;
    }

    public function markDirty()
    {
        $this->dirty = true;

        return $this;
    }

    public function toHtml()
    {
        if ($this->dirty) {
            return $this->renderChildren();
        }

        return $this->source;
    }

    public function renderArenaChildren($parent)
    {
        $html = '';

        for ($child = $this->arena->firstChild($parent); $child !== null; $child = $this->arena->next($child)) {
            if (isset($this->arena->sourceAnchor[$child])) {
                continue;
            }

            $html .= $this->renderArenaNode($child);
        }

        return $html;
    }

    public function renderArenaNode($id)
    {
        $kind = $this->arena->kind($id);

        if (($kind & Arena::KIND_MASK) === Arena::SOURCE_ANCHOR) {
            $target = $this->arena->anchorTarget[$id] ?? null;

            if ($target === null
                || ($this->arena->sourceAnchor[$target] ?? null) !== $id
                || $this->arena->parent($target) === null) {
                return '';
            }

            return $this->renderArenaNode($target);
        }

        if (isset($this->handles[$id])) {
            return $this->handles[$id]->toHtml();
        }

        if (($kind & Arena::KIND_MASK) === Arena::ELEMENT) {
            $children = $this->renderArenaChildren($id);
            $opening = $this->arena->value[$id];

            if ($kind & Arena::SYNTHETIC) {
                $opening = '';
            }

            return $opening.$children.$this->arena->closing[$id];
        }

        return $this->arena->value[$id];
    }

    public function __toString()
    {
        return $this->toHtml();
    }
}
