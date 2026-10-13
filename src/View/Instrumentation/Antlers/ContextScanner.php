<?php

namespace Statamic\View\Instrumentation\Antlers;

use Statamic\View\Antlers\Language\Analyzers\Html\Document;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode as LanguageAntlersNode;
use Statamic\View\Antlers\Language\Nodes\LiteralNode;
use Statamic\View\Antlers\Language\Parser\ComponentCompiler;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Utilities\CharacterOffsets;
use Statamic\View\Instrumentation\ComponentPrefixes;
use Statamic\View\Instrumentation\HtmlContext;
use Statamic\View\Instrumentation\HtmlSpec;

/** @internal */
class ContextScanner
{
    protected bool $treatComponentsAsDynamic = false;

    /** @var string[] */
    protected array $componentPrefixes = ComponentPrefixes::DEFAULTS;

    protected ElementInspector $inspector;

    /** @var array<int, array<int, array{0: string, 1: string|null, 2: string}>> */
    protected array $embeddedPositions = [];

    /** @var array<string, string[]> */
    protected array $reportableChains = [];

    public function __construct()
    {
        $this->inspector = new ElementInspector;
    }

    public function treatComponentsAsDynamic(bool $dynamic = true): static
    {
        $this->treatComponentsAsDynamic = $dynamic;

        return $this;
    }

    /** @param  string[]  $prefixes */
    public function withComponentPrefixes(array $prefixes): static
    {
        $this->componentPrefixes = $prefixes;

        return $this;
    }

    /** @param  AbstractNode[]  $nodes */
    public function annotate(array $nodes, ?DocumentParser $parser = null, ?Document $document = null): void
    {
        $nodes = array_values($nodes);

        if ($parser === null) {
            foreach ($nodes as $node) {
                if ($node instanceof LanguageAntlersNode && $node->getParser() !== null) {
                    $parser = $node->getParser();

                    break;
                }
            }
        }

        if ($parser === null) {
            return;
        }

        $hasAnnotatableNodes = false;

        foreach ($nodes as $node) {
            if (! $node instanceof LiteralNode) {
                $hasAnnotatableNodes = true;

                break;
            }
        }

        if (! $hasAnnotatableNodes) {
            return;
        }

        $document ??= $this->treatComponentsAsDynamic
            ? Document::fromParser($parser, $this->componentPrefixes)
            : $parser->html();
        $placements = $document->regionPlacements();
        $source = (string) $parser->getParsedContent();
        $documentHasExplicitStructure = HtmlSpec::hasExplicitDocumentStructure($source);
        $this->classifyEmbeddedPlacements($placements, $source);
        $this->reportableChains = [];

        $pending = [];
        $byteOffsets = [];

        foreach ($nodes as $index => $node) {
            if ($node instanceof LiteralNode) {
                continue;
            }

            $placement = null;

            if ($node instanceof LanguageAntlersNode) {
                $placement = $placements[spl_object_id($node)] ?? null;
            }

            [$context, $startByte] = $this->contextFromPlacement($placement, $document, $source);
            $pending[$index] = [$context, $startByte];

            if ($startByte !== null) {
                $byteOffsets[] = $startByte;
            }
        }

        $characterOffsets = CharacterOffsets::toCharacters($source, $byteOffsets);
        $proxyDepth = 0;

        foreach ($pending as $index => [$context, $startByte]) {
            $node = $nodes[$index];

            if ($proxyDepth > 0) {
                $context = $this->dynamicComponentContext($context);
            } else {
                $context->elementStartOffset = null;

                if ($startByte !== null) {
                    $context->elementStartOffset = $characterOffsets[$startByte] ?? null;
                }
            }

            $context->documentHasExplicitStructure = $documentHasExplicitStructure;
            $this->applyContext($node, $context);

            if ($node instanceof LanguageAntlersNode && $this->isComponentProxyNode($node)) {
                if ($node->isClosingTag) {
                    $proxyDepth = max(0, $proxyDepth - 1);
                } elseif (! $node->isSelfClosing) {
                    $proxyDepth++;
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>|null  $placement
     * @return array{0: HtmlContext, 1: int|null}
     */
    protected function contextFromPlacement(?array $placement, Document $document, string $source): array
    {
        if ($placement === null) {
            return [new HtmlContext(HtmlContext::KIND_ELEMENT_CONTENT), null];
        }

        $chain = $this->reportableChain($placement['chain']);

        switch ($placement['bucket']) {
            case 'content':
            case 'raw-text':
                $context = new HtmlContext(
                    $this->contentKindForPlacement($placement['bucket']),
                    $this->innermostStaticName($chain),
                    null,
                    $chain,
                    null,
                    false,
                    $placement['reconstructed']
                );
                $context->fosterParented = $placement['fostered'];

                return [$context, $document->elementSourceStart($placement['parentElement'])];

            case 'embedded':
                [$kind, $attribute, $partial] = $this->classifyEmbedded($placement, $source);
                $reportableOwner = $this->reportableName($placement['ownerName']);

                if ($this->shouldAppendDynamicOwner($placement['ownerName'], $reportableOwner, $chain)) {
                    $chain[] = HtmlContext::DYNAMIC_ELEMENT;
                }

                $elementName = $this->embeddedElementName($kind, $partial, $reportableOwner);

                $context = new HtmlContext(
                    $kind,
                    $elementName,
                    $attribute,
                    $chain,
                    null,
                    $placement['closing']
                );

                return [$context, $document->elementSourceStart($placement['owner'])];

            case 'opaque':
            default:
                $type = $placement['type'];

                if ($type === 'comment') {
                    $context = new HtmlContext(HtmlContext::KIND_COMMENT, null, null, $chain);

                    return [$context, null];
                }

                if ($type === 'php') {
                    $context = new HtmlContext(HtmlContext::KIND_PHP, null, null, $chain);

                    return [$context, null];
                }

                if ($type !== 'markup') {
                    $context = new HtmlContext(HtmlContext::KIND_DOCTYPE, null, null, $chain);

                    return [$context, null];
                }

                [$kind, $attribute, $partial] = $this->classifyEmbedded($placement, $source);

                $context = new HtmlContext(
                    $kind,
                    $this->embeddedElementName($kind, $partial),
                    $attribute,
                    $chain,
                    null,
                    substr($source, $placement['markupStart'], 2) === '</'
                );

                return [$context, $placement['markupStart']];
        }
    }

    /**
     * @param  array<string, mixed>  $placement
     * @return array{0: string, 1: string|null, 2: string}
     */
    protected function classifyEmbedded(array $placement, string $source): array
    {
        $relative = $placement['regionStart'] - $placement['markupStart'];

        return $this->embeddedPositions[$placement['markupStart']][$relative];
    }

    protected function classifyEmbeddedPlacements(array $placements, string $source): void
    {
        $groups = [];

        foreach ($placements as $placement) {
            if ($placement['bucket'] !== 'embedded'
                && ! ($placement['bucket'] === 'opaque' && $placement['type'] === 'markup')) {
                continue;
            }

            $start = $placement['markupStart'];
            $groups[$start]['end'] = $placement['markupEnd'];
            $groups[$start]['offsets'][] = $placement['regionStart'] - $start;
        }

        $this->embeddedPositions = [];

        foreach ($groups as $start => $group) {
            $this->embeddedPositions[$start] = $this->inspector->classifyTagPositions(
                substr($source, $start, $group['end'] - $start),
                $group['offsets']
            );
        }
    }

    /**
     * @param  array<int, string|null>  $chain
     * @return string[]
     */
    protected function reportableChain(array $chain): array
    {
        if ($chain === []) {
            return [];
        }

        $key = implode('/', $chain);

        if (isset($this->reportableChains[$key])) {
            return $this->reportableChains[$key];
        }

        $reportable = [];

        foreach ($chain as $name) {
            $reportable[] = $this->reportableName($name) ?? HtmlContext::DYNAMIC_ELEMENT;
        }

        return $this->reportableChains[$key] = $reportable;
    }

    protected function reportableName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        if ($this->treatComponentsAsDynamic && ComponentPrefixes::matches($name, $this->componentPrefixes)) {
            return null;
        }

        return $name;
    }

    /** @param  string[]  $chain */
    protected function innermostStaticName(array $chain): ?string
    {
        if ($chain === []) {
            return null;
        }

        $innermost = $chain[count($chain) - 1];

        if ($innermost === HtmlContext::DYNAMIC_ELEMENT) {
            return null;
        }

        return $innermost;
    }

    protected function contentKindForPlacement(string $bucket): string
    {
        if ($bucket === 'raw-text') {
            return HtmlContext::KIND_RAW_TEXT;
        }

        return HtmlContext::KIND_ELEMENT_CONTENT;
    }

    protected function shouldAppendDynamicOwner(?string $ownerName, ?string $reportableOwner, array $chain): bool
    {
        if ($ownerName === null || $reportableOwner !== null) {
            return false;
        }

        return $chain === [] || $chain[count($chain) - 1] !== HtmlContext::DYNAMIC_ELEMENT;
    }

    protected function embeddedElementName(string $kind, string $partial, ?string $fallback = null): ?string
    {
        if ($kind === HtmlContext::KIND_ELEMENT_NAME && $partial !== '') {
            return $partial;
        }

        return $fallback;
    }

    protected function dynamicComponentContext(HtmlContext $context): HtmlContext
    {
        $stack = $context->elementStack;

        if ($stack === [] || $stack[count($stack) - 1] !== HtmlContext::DYNAMIC_ELEMENT) {
            $stack[] = HtmlContext::DYNAMIC_ELEMENT;
        }

        return new HtmlContext(
            HtmlContext::KIND_ELEMENT_CONTENT,
            null,
            null,
            $stack
        );
    }

    protected function isComponentProxyNode(LanguageAntlersNode $node): bool
    {
        return ComponentCompiler::isComponentProxyContent((string) $node->content);
    }

    protected function applyContext(AbstractNode $node, HtmlContext $context): void
    {
        if (! $node instanceof LanguageAntlersNode) {
            return;
        }

        $node->htmlContext = $context;

        foreach ($node->processedInterpolationRegions as $nodes) {
            foreach ($nodes as $interpolationNode) {
                if ($interpolationNode instanceof LiteralNode) {
                    continue;
                }

                $this->applyContext($interpolationNode, clone $context);
            }
        }
    }
}
