<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Tappable;
use Statamic\View\Antlers\Language\Analyzers\NodeTypeAnalyzer;
use Statamic\View\Antlers\Language\Nodes\AntlersNode as LanguageAntlersNode;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Runtime\EnvironmentDetails;
use Statamic\View\Antlers\Language\Utilities\CharacterOffsets;
use Statamic\View\Instrumentation\ComponentPrefixes;
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

    private const MAX_CHAIN_DEPTH = 512;

    protected static array $voidElements = HtmlSpec::VOID_ELEMENTS;

    protected static array $rawTextElements = HtmlSpec::RAW_TEXT_ELEMENTS;

    protected static array $pClosingElements = HtmlSpec::P_CLOSING_ELEMENTS;

    protected static array $impliedCloseTargets = HtmlSpec::IMPLIED_CLOSE_TARGETS;

    protected static array $scopeBoundaries = HtmlSpec::SCOPE_BOUNDARIES;

    protected static array $listItemScopeBoundaries = HtmlSpec::LIST_ITEM_SCOPE_BOUNDARIES;

    protected static array $buttonScopeBoundaries = HtmlSpec::BUTTON_SCOPE_BOUNDARIES;

    protected static array $tableScopeBoundaries = HtmlSpec::TABLE_SCOPE_BOUNDARIES;

    protected static array $tableScopedStarts = HtmlSpec::TABLE_SCOPED_STARTS;

    protected static array $tableStructureStarts = HtmlSpec::TABLE_STRUCTURE_STARTS;

    protected static array $headingElements = HtmlSpec::HEADING_ELEMENTS;

    protected static array $scopedEndTags = HtmlSpec::SCOPED_END_TAGS;

    protected static array $tableSections = HtmlSpec::TABLE_SECTIONS;

    protected static array $tableStructuralElements = HtmlSpec::TABLE_STRUCTURAL_ELEMENTS;

    protected static array $allowedInTable = HtmlSpec::ALLOWED_IN_TABLE;

    protected static array $selectTableElements = HtmlSpec::SELECT_TABLE_ELEMENTS;

    protected static array $svgHtmlIntegrationPoints = HtmlSpec::SVG_HTML_INTEGRATION_POINTS;

    protected static array $mathMlTextIntegrationPoints = HtmlSpec::MATHML_TEXT_INTEGRATION_POINTS;

    protected static array $foreignBreakoutElements = HtmlSpec::FOREIGN_BREAKOUT_ELEMENTS;

    protected static array $formattingElements = HtmlSpec::FORMATTING_ELEMENTS;

    protected static array $formattingMarkerElements = HtmlSpec::FORMATTING_MARKER_ELEMENTS;

    protected static array $formattingBreakStarts = HtmlSpec::FORMATTING_BREAK_STARTS;

    protected static array $svgTagNames = HtmlSpec::SVG_TAG_NAMES;

    protected Document $document;

    protected Arena $arena;

    protected string $source;

    protected bool $dirty = false;

    /** @var array<int, int> */
    protected array $antlersNodes = [];

    /** @var array<int, Node> */
    protected array $handles = [];

    /** @var array<int, int> */
    protected array $regionEnds = [];

    /** @var array<int, LanguageAntlersNode> */
    protected array $regionNodes = [];

    /** @var int[] */
    protected array $regionStarts = [];

    protected int $regionCursor = 0;

    /** @var int[] */
    protected array $stack = [];

    /** @var array<int, int|null> */
    protected array $activeFormatting = [];

    /** @var array<string, int> */
    protected array $activeSignatureCounts = [];

    /** @var array<int, array<string, int>> */
    protected array $activeSignatureCountStack = [];

    /** @var array<int, string> */
    protected array $formattingSignatures = [];

    protected int $scriptEscape = self::SCRIPT_DATA;

    protected ?int $openSelect = null;

    protected bool $selectInTable = false;

    protected ?int $formElement = null;

    protected ?int $textElement = null;

    protected ?int $rawTextClosingElement = null;

    protected int $rawTextClosingOffset = 0;

    /** @var array<string, int> html element name => number of those elements on the open stack */
    protected array $openElementCounts = [];

    /** @var array<string, mixed> answers derived from the open stack, cleared whenever it changes */
    protected array $stackFacts = [];

    /** @var array<int, array{0: array<int, string|null>, 1: int|null}> container id => insertion chain */
    protected array $insertionChains = [];

    protected int $tableStartDepth = 0;

    /** @var array<int, TableMode> template element id => current template insertion mode */
    protected array $templateInsertionModes = [];

    /** @var array<int, array<string, mixed>> keyed by spl_object_id of the language node */
    protected array $regionPlacements = [];

    /** @var array<int, int> element id => byte offset of its opening `<` */
    protected array $elementSourceStarts = [];

    protected string $authoredSource;

    /** @var array<int, array{offset: int, line: int}>|null keyed by normalized byte offset */
    protected ?array $authoredPositions = null;

    protected ?bool $mapsAuthoredSource = null;

    /** @var int[] */
    protected array $collapsedLineEndings = [];

    protected int $collapsedCursor = 0;

    protected ?string $insertedLineEnding = null;

    protected array $componentPrefixes = [];

    private function __construct(string $source, ?string $authoredSource = null)
    {
        $this->document = $this;
        $this->arena = new Arena();
        $this->source = $source;
        $this->authoredSource = $authoredSource ?? $source;
    }

    public function arena(): Arena
    {
        return $this->arena;
    }

    /** @return array<int, array<string, mixed>> */
    public function regionPlacements(): array
    {
        return $this->regionPlacements;
    }

    public function elementSourceStart(?int $id): ?int
    {
        if ($id === null) {
            return null;
        }

        return $this->elementSourceStarts[$id] ?? null;
    }

    public function elementSourceCharacterOffset(?int $id): ?int
    {
        return $this->authoredElementPosition($id)['offset'] ?? null;
    }

    public function elementSourceLine(?int $id): ?int
    {
        return $this->authoredElementPosition($id)['line'] ?? null;
    }

    /** @return array{offset: int, line: int}|null */
    protected function authoredElementPosition(?int $id): ?array
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

    protected function resolveAuthoredPositions(): void
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

    protected function recordPlacement(LanguageAntlersNode $node, array $facts): void
    {
        $identity = spl_object_id($node);

        if (! isset($this->regionPlacements[$identity])) {
            $this->regionPlacements[$identity] = $facts;
        }
    }

    /** @return array{0: array<int, string|null>, 1: int|null} */
    protected function insertionChain(?int $id): array
    {
        if ($id === null || $id === 0) {
            return [[], null];
        }

        if (isset($this->insertionChains[$id])) {
            return $this->insertionChains[$id];
        }

        $pending = [];

        for ($ancestor = $id; $ancestor !== null && $ancestor !== 0 && ! isset($this->insertionChains[$ancestor]); $ancestor = $this->arena->parents[$ancestor]) {
            $pending[] = $ancestor;
        }

        [$chain, $innermost] = $ancestor === null || $ancestor === 0 ? [[], null] : $this->insertionChains[$ancestor];

        for ($index = count($pending) - 1; $index >= 0; $index--) {
            $node = $pending[$index];

            if (($this->arena->kinds[$node] & Arena::KIND_MASK) === Arena::ELEMENT) {
                if (count($chain) >= self::MAX_CHAIN_DEPTH) {
                    $chain = array_slice($chain, 0, self::MAX_CHAIN_DEPTH - 1);
                }

                $chain[] = $this->arena->name[$node];

                if (isset($this->elementSourceStarts[$node])) {
                    $innermost = $node;
                }
            }
        }

        return $this->insertionChains[$id] = [$chain, $innermost];
    }

    public function handle(int $id): Node|static
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

    public static function fromParser(DocumentParser $parser, array $componentPrefixes = []): self
    {
        $document = new self($parser->getParsedContent(), $parser->getOriginalContent());
        $document->componentPrefixes = $componentPrefixes;
        $document->build($parser->getNodes());

        return $document;
    }

    public static function parse(string $template): self
    {
        if (NodeTypeAnalyzer::$environmentDetails === null) {
            NodeTypeAnalyzer::$environmentDetails = app(EnvironmentDetails::class);
        }

        $parser = new DocumentParser();
        $parser->parse($template);

        return $parser->html();
    }

    public static function isVoidElement(?string $name): bool
    {
        return is_string($name) && isset(self::$voidElements[strtolower($name)]);
    }

    public function isComponentElementName(?string $name): bool
    {
        return $name !== null && ComponentPrefixes::matches($name, $this->componentPrefixes);
    }

    public function createArenaElement(string $name): Element
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

    public function parent(): ?Node
    {
        return null;
    }

    public function node(LanguageAntlersNode $node): ?AntlersNode
    {
        $id = $this->antlersNodes[spl_object_id($node)] ?? null;

        if ($id === null) {
            return null;
        }

        return $this->handle($id);
    }

    /** @return Collection<int, Element> */
    public function elements(string ...$names): Collection
    {
        return $this->arenaDescendants($names);
    }

    /** @return LazyCollection<int, Region> */
    public function regions(?string $name = null): LazyCollection
    {
        return new LazyCollection(function () use ($name) {
            foreach ($this->regionStarts as $start) {
                $node = $this->regionNodes[$start];

                if (! $node instanceof LanguageAntlersNode || ! $this->isRegionOpening($node, $name)) {
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

    protected function isRegionOpening(LanguageAntlersNode $node, ?string $name): bool
    {
        if ($node->isClosingTag) {
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

    public function isDirty(): bool
    {
        return $this->dirty;
    }

    public function markDirty(): static
    {
        $this->dirty = true;

        return $this;
    }

    public function toHtml(): string
    {
        if (! $this->dirty) {
            return $this->authoredSource;
        }

        return $this->renderChildren();
    }

    protected function mapsAuthoredSource(): bool
    {
        if ($this->mapsAuthoredSource !== null) {
            return $this->mapsAuthoredSource;
        }

        if ($this->authoredSource === $this->source) {
            return $this->mapsAuthoredSource = false;
        }

        $collapsed = [];
        $position = 0;

        while (($position = strpos($this->authoredSource, "\r\n", $position)) !== false) {
            $collapsed[] = $position - count($collapsed);
            $position += 2;
        }

        if (strlen($this->authoredSource) - count($collapsed) !== strlen($this->source)) {
            return $this->mapsAuthoredSource = false;
        }

        $this->collapsedLineEndings = $collapsed;
        $lineFeeds = substr_count($this->authoredSource, "\n");
        $carriageReturns = substr_count($this->authoredSource, "\r");

        if ($collapsed !== [] && $lineFeeds === count($collapsed) && $carriageReturns === count($collapsed)) {
            $this->insertedLineEnding = "\r\n";
        } elseif ($lineFeeds === 0 && $carriageReturns > 0) {
            $this->insertedLineEnding = "\r";
        }

        return $this->mapsAuthoredSource = true;
    }

    protected function authoredOffset(int $normalized): int
    {
        $count = count($this->collapsedLineEndings);
        $cursor = $this->collapsedCursor;

        if ($cursor > 0 && $this->collapsedLineEndings[$cursor - 1] >= $normalized) {
            $high = $cursor;
            $low = 0;
        } elseif ($cursor === $count || $this->collapsedLineEndings[$cursor] >= $normalized) {
            return $normalized + $cursor;
        } elseif ($cursor + 1 === $count || $this->collapsedLineEndings[$cursor + 1] >= $normalized) {
            $this->collapsedCursor = $cursor + 1;

            return $normalized + $cursor + 1;
        } else {
            $low = $cursor + 1;
            $high = $count;
        }

        while ($low < $high) {
            $middle = ($low + $high) >> 1;

            if ($this->collapsedLineEndings[$middle] < $normalized) {
                $low = $middle + 1;
            } else {
                $high = $middle;
            }
        }

        $this->collapsedCursor = $low;

        return $normalized + $low;
    }

    protected function sourceSlice(int $start, int $length): string
    {
        $from = $this->authoredOffset($start);
        $to = $this->authoredOffset($start + $length);

        return substr($this->authoredSource, $from, $to - $from);
    }

    public function adoptLineEndings(string $markup): string
    {
        if (! $this->mapsAuthoredSource() || $this->insertedLineEnding === null || ! str_contains($markup, "\n")) {
            return $markup;
        }

        if ($this->insertedLineEnding === "\r\n") {
            return preg_replace('/(?<!\r)\n/', "\r\n", $markup) ?? $markup;
        }

        return str_replace(["\r\n", "\n"], "\r", $markup);
    }

    public function valueMarkup(int $id): string
    {
        $value = $this->arena->value[$id];

        if (! str_contains($value, "\n") || ! $this->mapsAuthoredSource()) {
            return $value;
        }

        $start = $this->arena->sourceStart[$id] ?? null;

        if ($start === null) {
            return $this->adoptLineEndings($value);
        }

        return $this->sourceSlice($start, strlen($value));
    }

    public function closingMarkup(int $id): string
    {
        $closing = $this->arena->closing[$id] ?? '';

        if (! str_contains($closing, "\n") || ! $this->mapsAuthoredSource()) {
            return $closing;
        }

        $start = $this->arena->closingStart[$id] ?? null;

        if ($start === null) {
            return $this->adoptLineEndings($closing);
        }

        return $this->sourceSlice($start, strlen($closing));
    }

    public function renderArenaChildren(int $parent): string
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

    public function renderArenaNode(int $id): string
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
            $opening = $kind & Arena::SYNTHETIC ? '' : $this->valueMarkup($id);

            return $opening.$children.$this->closingMarkup($id);
        }

        return $this->valueMarkup($id);
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }
}
