<?php

namespace Statamic\View\Instrumentation\Antlers;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\EscapedContentNode;
use Statamic\View\Antlers\Language\Parser\ComponentCompiler;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Utilities\CharacterOffsets;
use Statamic\View\Instrumentation\ComponentPrefixes;
use Statamic\View\Instrumentation\HtmlContext;
use Statamic\View\Instrumentation\InstrumentationState;
use Statamic\View\Instrumentation\Span;
use Statamic\View\Instrumentation\TemplateRegion;

/** @internal */
class TemplateAnalyzer
{
    protected ElementInspector $inspector;

    /** @var string[] */
    protected array $componentPrefixes;

    /** @param  string[]|null  $componentPrefixes */
    public function __construct(?array $componentPrefixes = null)
    {
        $this->inspector = new ElementInspector;
        $this->componentPrefixes = $componentPrefixes ?? ComponentPrefixes::DEFAULTS;
    }

    /**
     * @return array{regions: TemplateRegion[], elements: array<int, array{insertAt: int, hasAttribute: callable}>, contexts: array<int, HtmlContext|null>}
     */
    public function analyze(string $template, bool $collectElements = true, bool $commentsAtDocumentRoot = true, ?ComponentSourceMap $sourceMap = null): array
    {
        $parser = new DocumentParser;
        $parser->inheritRuntimeLineSeed(false);
        $parser->parse($template);
        $nodes = $parser->getNodes();
        $lineOffset = InstrumentationState::lineSeed() - 1;
        $characterOffset = InstrumentationState::offsetSeed();

        (new ContextScanner)
            ->treatComponentsAsDynamic()
            ->withComponentPrefixes($this->componentPrefixes)
            ->annotate($nodes, $parser);

        $pending = [];
        $characterOffsets = [];
        $contexts = [];

        foreach ($nodes as $node) {
            if ($node instanceof AntlersNode) {
                $contexts[] = $node->htmlContext;
            }

            if ($this->shouldSkipNode($node)) {
                continue;
            }

            $context = $node->htmlContext;

            if ($context === null) {
                continue;
            }

            $start = $node->startPosition->offset;
            $end = $node->endPosition->offset + 1;
            $commentSafe = $context->isSafeForHtmlComments()
                && ($commentsAtDocumentRoot || $context->elementStack !== []);
            $isPaired = $node->isPaired();

            if ($isPaired) {
                $closer = $this->closingBoundaryFor($node);
                $closingContext = null;

                if ($closer !== null) {
                    $end = $closer->endPosition->offset + 1;
                    $closingContext = $closer->htmlContext;
                }

                $commentSafe = $commentSafe
                    && $closingContext !== null
                    && $closingContext->isSafeForHtmlComments()
                    && $context->sharesElementContainerWith($closingContext);
            }

            $characterOffsets[] = $start;
            $characterOffsets[] = $end;

            if ($this->canCollectElement($collectElements, $context)) {
                $characterOffsets[] = $context->elementStartOffset;
            }

            $pending[] = [$node, $context, $start, $end, $commentSafe, $isPaired];
        }

        if ($pending === []) {
            return ['regions' => [], 'elements' => [], 'contexts' => $contexts];
        }

        [$byteOffsets, $sourceCharacterOffsets] = CharacterOffsets::normalizedToBytesAndCharacters($template, $characterOffsets);
        $regions = [];
        $elements = [];
        [$mappedStarts, $mappedEnds] = $sourceMap?->positions(array_column($pending, 2), array_column($pending, 3)) ?? [[], []];

        foreach ($pending as [$node, $context, $start, $end, $commentSafe, $isPaired]) {
            $insideCharacterReference = $context->isSafeForHtmlComments()
                && $this->continuesCharacterReference($template, $byteOffsets[$start]);

            $regions[] = new TemplateRegion(
                Span::ENGINE_ANTLERS,
                $this->regionType($isPaired),
                $this->regionExpression($node),
                ($mappedStarts[$start]['line'] ?? $node->startPosition->line) + $lineOffset,
                ($mappedStarts[$start]['offset'] ?? $sourceCharacterOffsets[$byteOffsets[$start]]) + $characterOffset,
                ($mappedEnds[$end]['offset'] ?? $sourceCharacterOffsets[$byteOffsets[$end]]) + $characterOffset,
                $byteOffsets[$start],
                $byteOffsets[$end],
                $context,
                $commentSafe && ! $insideCharacterReference,
                $insideCharacterReference
            );

            if ($this->canCollectElement($collectElements, $context)
                && ! isset($elements[$context->elementStartOffset])) {
                $elements[$context->elementStartOffset] = $this->elementFacts(
                    $template,
                    $context,
                    $byteOffsets[$context->elementStartOffset]
                );
            }
        }

        return ['regions' => $regions, 'elements' => $elements, 'contexts' => $contexts];
    }

    protected function continuesCharacterReference(string $template, int $byteStart): bool
    {
        $length = min($byteStart, 34);

        if ($length === 0) {
            return false;
        }

        return preg_match('/&#?[A-Za-z0-9]*$/D', substr($template, $byteStart - $length, $length)) === 1;
    }

    protected function shouldSkipNode(AbstractNode $node): bool
    {
        if (! $node instanceof AntlersNode || $node instanceof EscapedContentNode) {
            return true;
        }

        if ($node->isComment || $node->isClosingTag || $this->isComponentProxyNode($node)) {
            return true;
        }

        return ($node->name->name ?? null) === 'noparse';
    }

    protected function canCollectElement(bool $collectElements, HtmlContext $context): bool
    {
        if (! $collectElements) {
            return false;
        }

        if ($context->elementStartOffset === null) {
            return false;
        }

        return $context->canInstrumentElement();
    }

    protected function regionExpression(AntlersNode $node): string
    {
        if ($node->interpolationRegions === []) {
            return trim($node->content);
        }

        $raw = $node->getNodeDocumentText();
        $inner = substr($raw, strlen($node->rawStart), strlen($raw) - strlen($node->rawStart) - strlen($node->rawEnd));

        return trim($inner === '' ? $node->content : $inner);
    }

    protected function regionType(bool $isPaired): string
    {
        if ($isPaired) {
            return TemplateRegion::TYPE_PAIR;
        }

        return TemplateRegion::TYPE_SINGLE;
    }

    protected function isComponentProxyNode(AntlersNode $node): bool
    {
        return ComponentCompiler::isComponentProxyContent((string) $node->content);
    }

    /** @return array{insertAt: int, hasAttribute: callable} */
    protected function elementFacts(string $template, HtmlContext $context, int $byteStart): array
    {
        $openingTag = $this->inspector->openingTagAt($template, $context->elementStartOffset, $byteStart);
        $inspector = $this->inspector;

        return [
            'insertAt' => $this->inspector->attributeInsertionOffset($template, $byteStart),
            'hasAttribute' => fn ($name) => $inspector->hasAttribute($openingTag, $name),
        ];
    }

    protected function closingBoundaryFor(AntlersNode $node): ?AntlersNode
    {
        $closer = $node->isClosedBy;
        $visited = [];

        while ($closer instanceof AntlersNode && $closer->isClosedBy instanceof AntlersNode) {
            $identity = spl_object_id($closer);

            if (isset($visited[$identity])) {
                return null;
            }

            $visited[$identity] = true;
            $closer = $closer->isClosedBy;
        }

        if (! $closer instanceof AntlersNode) {
            return null;
        }

        return $closer;
    }
}
