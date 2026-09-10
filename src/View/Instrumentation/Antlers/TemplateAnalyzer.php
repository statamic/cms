<?php

namespace Statamic\View\Instrumentation\Antlers;

use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\EscapedContentNode;
use Statamic\View\Antlers\Language\Parser\ComponentCompiler;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Utilities\CharacterOffsets;
use Statamic\View\Instrumentation\ComponentPrefixes;
use Statamic\View\Instrumentation\HtmlContext;
use Statamic\View\Instrumentation\Span;
use Statamic\View\Instrumentation\TemplateRegion;

/** @internal */
class TemplateAnalyzer
{
    /** @var ElementInspector */
    protected $inspector;

    /** @var string[] */
    protected $componentPrefixes;

    /** @param  string[]|null  $componentPrefixes */
    public function __construct(?array $componentPrefixes = null)
    {
        $this->inspector = new ElementInspector;
        $this->componentPrefixes = $componentPrefixes ?? ComponentPrefixes::DEFAULTS;
    }

    /**
     * @param  string  $template
     * @param  bool  $collectElements  Whether attribute layers are configured.
     * @return array{regions: TemplateRegion[], elements: array<int, array{insertAt: int, hasAttribute: callable}>}
     */
    public function analyze($template, $collectElements = true, $commentsAtDocumentRoot = true, ?ComponentSourceMap $sourceMap = null)
    {
        $parser = new DocumentParser;
        $parser->inheritRuntimeLineSeed(false);
        $parser->parse($template);
        $nodes = $parser->getNodes();

        (new ContextScanner)
            ->treatComponentsAsDynamic()
            ->withComponentPrefixes($this->componentPrefixes)
            ->annotate($nodes, $parser);

        $pending = [];
        $characterOffsets = [];

        foreach ($nodes as $node) {
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
            return ['regions' => [], 'elements' => []];
        }

        [$byteOffsets, $sourceCharacterOffsets] = CharacterOffsets::normalizedToBytesAndCharacters($template, $characterOffsets);
        $regions = [];
        $elements = [];
        [$mappedStarts, $mappedEnds] = $sourceMap?->positions(array_column($pending, 2), array_column($pending, 3)) ?? [[], []];

        foreach ($pending as [$node, $context, $start, $end, $commentSafe, $isPaired]) {
            $regions[] = new TemplateRegion(
                Span::ENGINE_ANTLERS,
                $this->regionType($isPaired),
                trim($node->content),
                $mappedStarts[$start]['line'] ?? $node->startPosition->line,
                $mappedStarts[$start]['offset'] ?? $sourceCharacterOffsets[$byteOffsets[$start]],
                $mappedEnds[$end]['offset'] ?? $sourceCharacterOffsets[$byteOffsets[$end]],
                $byteOffsets[$start],
                $byteOffsets[$end],
                $context,
                $node,
                $commentSafe
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

        return ['regions' => $regions, 'elements' => $elements];
    }

    protected function shouldSkipNode($node)
    {
        if (! $node instanceof AntlersNode || $node instanceof EscapedContentNode) {
            return true;
        }

        if ($node->isComment || $node->isClosingTag || $this->isComponentProxyNode($node)) {
            return true;
        }

        return ($node->name->name ?? null) === 'noparse';
    }

    protected function canCollectElement($collectElements, HtmlContext $context)
    {
        if (! $collectElements) {
            return false;
        }

        if ($context->elementStartOffset === null) {
            return false;
        }

        return $context->canInstrumentElement();
    }

    protected function regionType($isPaired)
    {
        if ($isPaired) {
            return TemplateRegion::TYPE_PAIR;
        }

        return TemplateRegion::TYPE_SINGLE;
    }

    protected function isComponentProxyNode(AntlersNode $node)
    {
        return ComponentCompiler::isComponentProxyContent((string) $node->content);
    }

    /**
     * @param  string  $template
     * @param  int  $byteStart
     * @return array{insertAt: int, hasAttribute: callable}
     */
    protected function elementFacts($template, HtmlContext $context, $byteStart)
    {
        $openingTag = $this->inspector->openingTagAt($template, $context->elementStartOffset, $byteStart);
        $inspector = $this->inspector;

        return [
            'insertAt' => $this->inspector->attributeInsertionOffset($template, $byteStart),
            'hasAttribute' => fn ($name) => $inspector->hasAttribute($openingTag, $name),
        ];
    }

    /** @return AntlersNode|null */
    protected function closingBoundaryFor(AntlersNode $node)
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
