<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html\Concerns;

use Statamic\View\Antlers\Language\Analyzers\Html\Arena;
use Statamic\View\Antlers\Language\Analyzers\Html\TableMode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode as LanguageAntlersNode;
use Statamic\View\Instrumentation\ComponentPrefixes;
use Statamic\View\Instrumentation\HtmlSpec;

/** @internal */
trait BuildsDocumentGraph
{
    protected function nextTextBoundary(int $position, int $sourceLength): int
    {
        $nextMarkup = strpos($this->source, '<', $position);

        if ($nextMarkup === false) {
            $nextMarkup = $sourceLength;
        }

        $nextAntlers = $sourceLength;

        if (! empty($this->regionStarts)) {
            $regionStart = $this->nextRegionStart($position);

            if ($regionStart !== null) {
                $nextAntlers = $regionStart;
            }
        }

        return min($nextMarkup, $nextAntlers);
    }

    protected function startsBogusClosingTag(int $start, ?string $next): bool
    {
        if ($next !== '/') {
            return false;
        }

        $nameStart = $this->source[$start + 2] ?? null;

        if ($nameStart === '>') {
            return false;
        }

        if (HtmlSpec::isAsciiAlpha($nameStart)) {
            return false;
        }

        return ! isset($this->regionEnds[$start + 2]);
    }

    protected function startsLiteralLessThan(int $start, ?string $next): bool
    {
        if ($next === null || HtmlSpec::isAsciiAlpha($next)) {
            return false;
        }

        if (in_array($next, ['!', '?', '/'], true)) {
            return false;
        }

        return ! isset($this->regionEnds[$start + 1]);
    }

    protected function endTagContext(string $name): array
    {
        $processAsHtml = ! $this->currentContainerIsForeign();
        $foreignMatch = null;

        if (! $processAsHtml && in_array($name, ['br', 'p'], true)) {
            $this->exitForeignContent();
            $processAsHtml = true;
        }

        if ($processAsHtml) {
            return [$processAsHtml, $foreignMatch];
        }

        for ($index = count($this->stack) - 1; $index >= 0; $index--) {
            $element = $this->stack[$index];

            if (! $this->isForeignElement($element)) {
                return [true, null];
            }

            if (strcasecmp((string) $this->arena->name[$element], $name) === 0) {
                return [false, $index];
            }
        }

        return [false, null];
    }

    protected function closesCurrentTextElement(string $name): bool
    {
        if ($this->textElement === null) {
            return false;
        }

        return strcasecmp((string) $this->arena->name[$this->textElement], $name) === 0;
    }

    protected function shouldCloseSelectBeforeEndTag(string $name): bool
    {
        if ($this->openSelect === null || ! $this->selectInTable) {
            return false;
        }

        if (! $this->isInHtmlSelectMode()) {
            return false;
        }

        return isset(self::$selectTableElements[$name]);
    }

    protected function shouldIgnoreEndTagInSelect(string $name, bool $closesCurrentTextElement): bool
    {
        if ($this->openSelect === null || ! $this->isInHtmlSelectMode()) {
            return false;
        }

        if ($closesCurrentTextElement) {
            return false;
        }

        return ! in_array($name, ['option', 'optgroup', 'select', 'template'], true);
    }

    protected function shouldCloseColgroupBeforeEndTag(string $name, bool $processAsHtml): bool
    {
        if (! $processAsHtml) {
            return false;
        }

        if ($this->tableMode() !== TableMode::ColumnGroup) {
            return false;
        }

        return ! in_array($name, ['col', 'colgroup', 'template'], true);
    }

    protected function isHtmlFormEndTag(string $name, bool $processAsHtml): bool
    {
        if (! $processAsHtml || $name !== 'form') {
            return false;
        }

        return ! $this->hasOpenHtmlTemplate();
    }

    protected function consumeFormEndTag(int $start, int $end, string $markup): void
    {
        if ($this->formElement === null) {
            $this->appendOpaqueMarkup($start, $end, $markup);

            return;
        }

        $formIndex = array_search($this->formElement, $this->stack, true);

        if ($formIndex !== false && ! $this->stackIndexIsInScope($formIndex, self::$scopeBoundaries)) {
            $this->formElement = null;
            $this->appendOpaqueMarkup($start, $end, $markup);

            return;
        }

        if ($formIndex !== false) {
            $tableIndex = $this->nearestTableIndex();

            if ($tableIndex === null || $formIndex > $tableIndex) {
                $this->generateImpliedEndTags();
                $formIndex = array_search($this->formElement, $this->stack, true);
            }

            if ($formIndex !== false) {
                $this->removeOpenElementAt($formIndex);
            }
        }

        $this->formElement = null;
        $this->appendOpaqueMarkup($start, $end, $markup);
    }

    protected function closeMatchedElement(int $match, string $name, string $markup, int $start, int $end): void
    {
        $matchedElement = $this->stack[$match];

        if (! empty($this->regionStarts)) {
            $this->attachEmbeddedRegions($matchedElement, $start, $end, true);
        }

        $this->setElementClosingMarkup($matchedElement, $markup, $start);

        if ($matchedElement === $this->textElement) {
            $this->textElement = null;
        }

        $clearsFormatting = ! $this->isForeignElement($matchedElement)
            && isset(self::$formattingMarkerElements[$this->arena->name[$matchedElement]]);
        $this->sliceOpenStack($match);

        if ($clearsFormatting) {
            $this->clearActiveFormattingToLastMarker();
        }

        if ($name === 'select') {
            $this->openSelect = null;
            $this->selectInTable = false;
        }

        if ($name === 'template') {
            unset($this->templateInsertionModes[$matchedElement]);
        }
    }

    protected function closeDynamicElement(int $start, int $end, string $markup): bool
    {
        for ($index = count($this->stack) - 1; $index >= 0; $index--) {
            $element = $this->stack[$index];

            if ($this->arena->name[$element] !== null) {
                continue;
            }

            $this->attachEmbeddedRegions($element, $start, $end, true);
            $this->setElementClosingMarkup($element, $markup, $start);
            $this->sliceOpenStack($index);

            return true;
        }

        return false;
    }

    protected function consumeStartTagInSelectMode(?string $name, int $start, int $end, string $markup): bool
    {
        if ($this->selectInTable && isset(self::$selectTableElements[$name ?? ''])) {
            $this->closeOpenElement('select');

            return false;
        }

        if ($name === 'select') {
            $this->closeOpenElement('select');
            $this->appendOpaqueMarkup($start, $end, $markup);

            return true;
        }

        if (in_array($name, ['input', 'keygen', 'textarea'], true)) {
            $this->closeOpenElement('select');

            return false;
        }

        if (in_array($name, ['option', 'optgroup', 'hr', 'script', 'template'], true)) {
            return false;
        }

        $this->appendOpaqueMarkup($start, $end, $markup);

        return true;
    }

    protected function shouldExitForeignContentBeforeStart(int $container, ?string $name, string $markup): bool
    {
        if ($container === 0 || ! $this->isForeignElement($container)) {
            return false;
        }

        return $this->shouldExitForeignContent($name, $markup);
    }

    protected function shouldIgnoreFrameStart(?string $name, bool $isHtmlElement, bool $processStartAsHtml): bool
    {
        if (! $isHtmlElement || ! $processStartAsHtml) {
            return false;
        }

        return in_array($name, ['frame', 'frameset'], true);
    }

    protected function initialFosterState(?string $name, string $namespace, int $sourceContainer, bool $processStartAsHtml): bool
    {
        if ($processStartAsHtml && $name !== null) {
            return $this->prepareTableStart($name);
        }

        if ($sourceContainer !== 0 && $this->isForeignElement($sourceContainer)) {
            return false;
        }

        return $namespace !== 'html' && $this->shouldFosterContent();
    }

    protected function isFosteredHiddenInput(int $element, ?string $name, bool $foster): bool
    {
        if (! $foster || $name !== 'input') {
            return false;
        }

        return strtolower((string) $this->handle($element)->attr('type')) === 'hidden';
    }

    protected function insertStartElement(
        int $element,
        int $sourceContainer,
        ?int $sourcePositionAnchor,
        ?int $sourceTable,
        ?int $sourceTableTail,
        bool $foster
    ): void {
        if ($foster) {
            $this->fosterNode($element, $sourceContainer);

            return;
        }

        $insertionContainer = $this->currentContainer();
        $this->arena->append($insertionContainer, $element);

        $this->anchorRecoveredSourceNode(
            $element,
            $insertionContainer,
            $sourceTable,
            $sourceTableTail,
            $this->arena->previous($element),
            $sourcePositionAnchor
        );
    }

    protected function pushStartElement(
        int $element,
        ?string $name,
        string $namespace,
        bool $selfClosing,
        bool $selectInTable,
        bool $tableForm
    ): void {
        if ($tableForm || ! $this->shouldPushElement($name, $namespace, $selfClosing)) {
            return;
        }

        $this->pushOpenElement($element);

        if ($namespace !== 'html') {
            return;
        }

        if ($name !== null && isset(self::$formattingElements[$name])) {
            $this->pushActiveFormattingElement($element);
        }

        if ($name === 'template') {
            $this->templateInsertionModes[$element] = TableMode::Template;
        }

        if ($name === 'select') {
            $this->openSelect = $element;
            $this->selectInTable = $selectInTable;
        }

        if (isset(self::$formattingMarkerElements[$name ?? ''])) {
            $this->pushActiveFormattingMarker();
        }

        if ($name === 'script') {
            $this->scriptEscape = self::SCRIPT_DATA;
        }

        if ($this->startsTextElement($name)) {
            $this->textElement = $element;
        }
    }

    protected function build(?array $nodes): void
    {
        if ($nodes !== null) {
            $this->indexAntlersByteRegions($nodes);
        }
        $this->regionCursor = 0;
        $sourceLength = strlen($this->source);
        $position = 0;

        while ($position < $sourceLength) {
            if (isset($this->regionEnds[$position])) {
                $position = $this->appendAntlers($position, $this->currentContainer());

                continue;
            }

            if ($this->textElement !== null && $this->arena->name[$this->textElement] === 'plaintext') {
                $this->appendTextWithAntlers($position, $sourceLength, $this->currentContainer());
                $position = $sourceLength;

                continue;
            }

            if ($this->textElement !== null && ! $this->isCurrentRawTextClosingTag($position)) {
                $next = $this->nextRawTextBoundary($position);
                $this->appendTextWithAntlers($position, $next, $this->currentContainer());
                $position = $next;

                continue;
            }

            if ($this->source[$position] !== '<') {
                $next = $this->nextTextBoundary($position, $sourceLength);
                $this->appendParsedText(substr($this->source, $position, $next - $position), $position);
                $position = $next;

                continue;
            }

            $position = $this->consumeMarkup($position);
        }

        $this->regionEnds = [];
        $this->resetOpenStack();
        $this->activeFormatting = [];
        $this->activeSignatureCounts = [];
        $this->activeSignatureCountStack = [];
        $this->formattingSignatures = [];
        $this->templateInsertionModes = [];
        $this->insertionChains = [];
    }

    protected function consumeMarkup(int $start): int
    {
        $next = $this->source[$start + 1] ?? null;

        if ($this->startsBogusClosingTag($start, $next)) {
            $end = $this->bogusCommentEnd($start);
            $this->appendOpaqueMarkup(
                $start,
                $end,
                substr($this->source, $start, $end - $start),
                'comment'
            );

            return $end;
        }

        if ($this->startsLiteralLessThan($start, $next)) {
            $this->appendParsedText('<', $start);

            return $start + 1;
        }

        $end = $this->markupEnd($start);
        $markup = substr($this->source, $start, $end - $start);

        if ($next === '!' || $next === '?') {
            $this->appendOpaqueMarkup($start, $end, $markup);

            return $end;
        }

        $closing = $next === '/';
        $name = $this->tagName($start, $end, $closing);

        if ($closing) {
            return $this->consumeEndTag($start, $end, $markup, $name);
        }

        if ($name === false) {
            $this->appendOpaqueMarkup($start, $end, $markup);

            return $end;
        }

        return $this->consumeStartTag($start, $end, $markup, $name);
    }

    protected function consumeStartTag(int $start, int $end, string $markup, ?string $name): int
    {
        $sourcePositionContainer = $this->currentContainer();
        $sourcePositionAnchor = $this->arena->sourceAnchor[$sourcePositionContainer] ?? null;

        $startsInHtmlSelectMode = $this->openSelect !== null && $this->isInHtmlSelectMode();

        if ($startsInHtmlSelectMode && $this->consumeStartTagInSelectMode($name, $start, $end, $markup)) {
            return $end;
        }

        if ($startsInHtmlSelectMode && $name === 'hr') {
            $this->closeImpliedElements('optgroup');
        }

        $sourceContainer = $this->currentContainer();
        [$sourceTable, $sourceTableTail] = $this->sourceTablePosition();

        if ($this->shouldExitForeignContentBeforeStart($sourceContainer, $name, $markup)) {
            $this->exitForeignContent();
            $sourceContainer = $this->currentContainer();
        }

        $processStartAsHtml = $this->startTagUsesHtmlRules($name, $sourceContainer);
        $namespace = $this->namespaceForStartTag($name, $sourceContainer);
        $isHtmlElement = $namespace === 'html';

        if ($isHtmlElement && $name === 'image') {
            $name = 'img';
        }

        if ($this->shouldIgnoreFrameStart($name, $isHtmlElement, $processStartAsHtml)) {
            $this->appendOpaqueMarkup($start, $end, $markup);

            return $end;
        }

        if ($isHtmlElement && $processStartAsHtml && $name !== null) {
            $this->prepareTemplateInsertionModeForStart($name);
        }

        if ($this->shouldIgnoreFormStart($name, $isHtmlElement)) {
            $this->appendOpaqueMarkup($start, $end, $markup);

            return $end;
        }

        if ($this->shouldIgnoreTableStructureStart($name, $isHtmlElement)) {
            $this->appendOpaqueMarkup($start, $end, $markup);

            return $end;
        }

        $selectInTable = $this->isTableSelectStart($name, $isHtmlElement);
        $foster = $this->initialFosterState($name, $namespace, $sourceContainer, $processStartAsHtml);
        $tableForm = $this->isTableFormStart($name, $isHtmlElement);

        if ($name !== null && $isHtmlElement) {
            $foster = $this->prepareFormattingForStart(
                $name,
                $foster,
                $tableForm,
                $startsInHtmlSelectMode
            );
        }

        if ($this->shouldReconstructFormattingBeforeStart($name, $processStartAsHtml)) {
            $this->reconstructActiveFormatting();

            if ($foster && $this->tableMode() === TableMode::Normal) {
                $foster = false;
            }
        }

        $selfClosing = $this->isSelfClosingTag($start, $end);
        $elementName = $this->elementNameForNamespace($name, $namespace);
        $element = $this->arena->element($elementName, $markup, $selfClosing, $namespace);
        $this->elementSourceStarts[$element] = $start;
        $this->arena->sourceStart[$element] = $start;
        if ($isHtmlElement && $name === 'form' && ! $this->hasOpenHtmlTemplate()) {
            $this->formElement = $element;
        }

        if ($tableForm || $this->isFosteredHiddenInput($element, $name, $foster)) {
            $foster = false;
        }

        $this->insertStartElement(
            $element,
            $sourceContainer,
            $sourcePositionAnchor,
            $sourceTable,
            $sourceTableTail,
            $foster
        );

        if ($this->regionStarts) {
            $this->attachEmbeddedRegions($element, $start, $end);
        }

        $this->pushStartElement(
            $element,
            $name,
            $namespace,
            $selfClosing,
            $selectInTable,
            $tableForm
        );

        return $end;
    }

    protected function consumeEndTag(int $start, int $end, string $markup, string|false|null $name): int
    {
        if (! is_string($name)) {
            if ($name === null && $this->closeDynamicElement($start, $end, $markup)) {
                return $end;
            }

            $this->appendOpaqueMarkup($start, $end, $markup);

            return $end;
        }

        [$processAsHtml, $foreignMatch] = $this->endTagContext($name);
        $closesCurrentTextElement = $this->closesCurrentTextElement($name);

        if ($this->shouldCloseSelectBeforeEndTag($name)) {
            if (! $this->hasOpenElementInTableScope($name)) {
                $this->appendOpaqueMarkup($start, $end, $markup);

                return $end;
            }

            $this->closeOpenElement('select');
        }

        if ($this->shouldIgnoreEndTagInSelect($name, $closesCurrentTextElement)) {
            $this->appendOpaqueMarkup($start, $end, $markup);

            return $end;
        }

        if ($this->shouldCloseColgroupBeforeEndTag($name, $processAsHtml)) {
            $this->popOpenElement();
        }

        if ($processAsHtml) {
            $this->prepareTableEnd($name);
        }

        if ($processAsHtml && in_array($name, ['frame', 'frameset'], true)) {
            $this->appendOpaqueMarkup($start, $end, $markup);

            return $end;
        }

        if ($processAsHtml && $name === 'br') {
            $this->appendRecoveredBreak($start, $end, $markup);

            return $end;
        }

        if ($this->isHtmlFormEndTag($name, $processAsHtml)) {
            $this->consumeFormEndTag($start, $end, $markup);

            return $end;
        }

        if ($processAsHtml
            && isset(self::$formattingElements[$name])
            && $this->closeFormattingElement($name, $markup, $start, $end)) {
            return $end;
        }

        if ($processAsHtml && $name === 'p') {
            $match = $this->htmlEndTagMatchIndex($name);

            if ($match === null) {
                $paragraph = $this->arena->element('p', '', false, 'html', true);
                $container = $this->currentContainer();

                if ($this->shouldFosterContent()) {
                    $this->fosterNode($paragraph, $container);
                } else {
                    $this->arena->append($container, $paragraph);
                }

                $this->pushOpenElement($paragraph);
                $match = count($this->stack) - 1;
            }
        } elseif ($processAsHtml) {
            $match = $this->htmlEndTagMatchIndex($name);
        } else {
            $match = $foreignMatch;
        }

        if ($match === null) {
            $this->appendOpaqueMarkup($start, $end, $markup);
        } else {
            $this->closeMatchedElement($match, $name, $markup, $start, $end);
        }

        return $end;
    }

    protected function appendRecoveredBreak(int $start, int $end, string $markup): void
    {
        $sourceContainer = $this->currentContainer();
        [$sourceTable, $sourceTableTail] = $this->sourceTablePosition();
        $foster = $this->prepareTableStart('br');

        if ($this->activeFormatting
            && $this->characterTokenUsesHtmlRules($this->currentContainer())) {
            $this->reconstructActiveFormatting();

            if ($foster && $this->tableMode() === TableMode::Normal) {
                $foster = false;
            }
        }

        $element = $this->arena->element('br', $markup, false, 'html');
        $this->elementSourceStarts[$element] = $start;
        $this->arena->sourceStart[$element] = $start;

        $this->insertStartElement(
            $element,
            $sourceContainer,
            $this->arena->sourceAnchor[$sourceContainer] ?? null,
            $sourceTable,
            $sourceTableTail,
            $foster
        );

        if ($this->regionStarts) {
            $this->attachEmbeddedRegions($element, $start, $end, true);
        }
    }

    protected function appendOpaqueMarkup(int $start, int $end, string $markup, ?string $forcedType = null): void
    {
        [$sourceTable, $sourceTableTail] = $this->sourceTablePosition();
        $type = $forcedType;

        if ($type === null) {
            $type = $this->opaqueMarkupType($markup);
        }

        $opaque = $this->arena->opaque($markup, $type);
        $this->arena->sourceStart[$opaque] = $start;
        $container = $this->currentContainer();
        $this->arena->append($container, $opaque);
        $this->anchorRecoveredSourceNode($opaque, $container, $sourceTable, $sourceTableTail);

        foreach ($this->regionsBetween($start, $end) as $region) {
            $graphNode = $this->makeAntlers($region['node'], $region['start'], $region['end']);
            $this->arena->embedded[$opaque][] = $graphNode;
            $this->arena->setParent($graphNode, $opaque);
            $this->arena->embeddedOwner[$graphNode] = $opaque;

            [$chain] = $this->insertionChain($this->arena->parent($opaque));

            $this->recordPlacement($region['node'], [
                'bucket' => 'opaque',
                'type' => $type,
                'chain' => $chain,
                'markupStart' => $start,
                'markupEnd' => $end,
                'regionStart' => $region['start'],
            ]);
        }
    }

    protected function appendTextWithAntlers(int $start, int $end, int $container): void
    {
        if ($this->shouldReconstructFormattingInPlaintext()) {
            $this->reconstructActiveFormatting();
            $container = $this->currentContainer();
        }

        $position = $start;

        while ($position < $end) {
            if (isset($this->regionEnds[$position])) {
                $position = $this->appendAntlers($position, $container);

                continue;
            }

            $next = $this->nextRegionStart($position);

            if ($next === null || $next > $end) {
                $next = $end;
            }

            $this->arena->append($container, $this->arena->text(substr($this->source, $position, $next - $position), $position));
            $position = $next;
        }
    }

    protected function shouldCloseImpliedElementsBeforeStart(string $name, bool $tableForm, bool $startsInHtmlSelectMode): bool
    {
        if ($tableForm || empty($this->stack)) {
            return false;
        }

        if ($startsInHtmlSelectMode && $name === 'hr') {
            return false;
        }

        if (isset(self::$pClosingElements[$name])) {
            return true;
        }

        if (isset(self::$impliedCloseTargets[$name])) {
            return true;
        }

        if (isset(self::$headingElements[$name])) {
            return true;
        }

        return $name === 'button';
    }

    protected function shouldIgnoreFormStart(?string $name, bool $isHtmlElement): bool
    {
        if (! $isHtmlElement || $name !== 'form' || $this->formElement === null) {
            return false;
        }

        return ! $this->hasOpenHtmlTemplate();
    }

    protected function shouldIgnoreTableStructureStart(?string $name, bool $isHtmlElement): bool
    {
        if (! $isHtmlElement || ! isset(self::$tableStructureStarts[$name ?? ''])) {
            return false;
        }

        return $this->tableStructureMode() === null && ! $this->hasOpenHtmlTemplate();
    }

    protected function isTableSelectStart(?string $name, bool $isHtmlElement): bool
    {
        if (! $isHtmlElement || $name !== 'select') {
            return false;
        }

        return in_array(
            $this->tableStructureMode(),
            [
                TableMode::Table,
                TableMode::TableBody,
                TableMode::Row,
                TableMode::Cell,
                TableMode::Caption,
                TableMode::ColumnGroup,
            ],
            true
        );
    }

    protected function isTableFormStart(?string $name, bool $isHtmlElement): bool
    {
        if (! $isHtmlElement || $name !== 'form') {
            return false;
        }

        return in_array($this->tableStructureMode(), [TableMode::Table, TableMode::TableBody, TableMode::Row], true);
    }

    protected function prepareFormattingForStart(string $name, bool $foster, bool $tableForm, bool $startsInHtmlSelectMode): bool
    {
        if ($name === 'nobr' && ! empty($this->activeFormatting)) {
            $this->reconstructActiveFormatting();

            if ($this->activeFormattingIndex($name) === null) {
                $stackIndex = $this->htmlElementInScopeIndex(
                    ['nobr' => true],
                    self::$scopeBoundaries
                );

                if ($stackIndex !== null) {
                    $this->sliceOpenStack($stackIndex);
                    $this->reconstructActiveFormatting();
                }
            }
        }

        $formattingIndex = $this->activeFormattingIndex($name);

        if (in_array($name, ['a', 'nobr'], true) && $formattingIndex !== null) {
            $formatting = $this->activeFormatting[$formattingIndex];
            $stackDepth = count($this->stack);
            $this->closeFormattingElement($name);
            $activeIndex = $this->activeFormattingPosition($formatting);

            if ($activeIndex !== false) {
                $this->removeActiveFormattingAt($activeIndex);
            }

            $stackIndex = $this->openStackIndex($formatting);

            if ($stackIndex !== false) {
                $this->removeOpenElementAt($stackIndex);
            }

            if ($this->shouldRefosterAfterStackChange($foster, $stackDepth)) {
                $foster = $this->prepareTableStart($name);
            }
        }

        $stackDepth = count($this->stack);

        if ($this->shouldCloseImpliedElementsBeforeStart($name, $tableForm, $startsInHtmlSelectMode)) {
            $this->closeImpliedElements($name);
        }

        if ($this->shouldRefosterAfterStackChange($foster, $stackDepth)) {
            $foster = $this->prepareTableStart($name);
        }

        return $foster;
    }

    protected function shouldRefosterAfterStackChange(bool $foster, int $previousStackDepth): bool
    {
        if ($foster || count($this->stack) === $previousStackDepth) {
            return false;
        }

        return $this->shouldFosterContent();
    }

    protected function shouldReconstructFormattingBeforeStart(?string $name, bool $processStartAsHtml): bool
    {
        if ($name === null || ! $processStartAsHtml || empty($this->activeFormatting)) {
            return false;
        }

        if (in_array($name, ['applet', 'button', 'marquee', 'object', 'select', 'xmp'], true)) {
            return true;
        }

        if (isset(self::$formattingBreakStarts[$name]) || isset(self::$rawTextElements[$name])) {
            return false;
        }

        return ! in_array($name, ['rb', 'rp', 'rt', 'rtc'], true);
    }

    protected function elementNameForNamespace(?string $name, string $namespace): ?string
    {
        if ($namespace !== 'svg' || ! isset(self::$svgTagNames[$name ?? ''])) {
            return $name;
        }

        return self::$svgTagNames[$name];
    }

    protected function shouldPushElement(?string $name, string $namespace, bool $selfClosing): bool
    {
        if ($namespace !== 'html'
            || $name === null
            || ComponentPrefixes::matches($name, $this->componentPrefixes)) {
            return ! $selfClosing;
        }

        return ! isset(self::$voidElements[$name]);
    }

    protected function startsTextElement(?string $name): bool
    {
        if ($name === null) {
            return false;
        }

        return isset(self::$rawTextElements[$name]) || $name === 'plaintext';
    }

    protected function opaqueMarkupType(string $markup): string
    {
        if (strncmp($markup, '<!--', 4) === 0) {
            return 'comment';
        }

        if (stripos($markup, '<!doctype') === 0) {
            return 'doctype';
        }

        if (strncmp($markup, '<!', 2) === 0) {
            return 'declaration';
        }

        if ($this->startsPhpBlock($markup, 0)) {
            return 'php';
        }

        if (strncmp($markup, '<?', 2) === 0) {
            return 'processing-instruction';
        }

        return 'markup';
    }

    protected function antlersPlacementBucket(): string
    {
        if ($this->textElement !== null) {
            return 'raw-text';
        }

        return 'content';
    }

    protected function shouldReconstructFormattingInPlaintext(): bool
    {
        if ($this->textElement === null || empty($this->activeFormatting)) {
            return false;
        }

        if ($this->arena->name[$this->textElement] !== 'plaintext') {
            return false;
        }

        return $this->characterTokenUsesHtmlRules($this->currentContainer());
    }

    protected function shouldReconstructFormattingAroundAntlers(): bool
    {
        if (empty($this->activeFormatting)) {
            return false;
        }

        if ($this->textElement !== null && $this->arena->name[$this->textElement] !== 'plaintext') {
            return false;
        }

        return $this->characterTokenUsesHtmlRules($this->currentContainer());
    }

    protected function shouldReconstructFormattingForText(bool $foster): bool
    {
        if (empty($this->activeFormatting)
            || ! $this->characterTokenUsesHtmlRules($this->currentContainer())) {
            return false;
        }

        return $foster || ! $this->shouldFosterContent();
    }

    protected function appendAntlers(int $start, int $container): int
    {
        $end = $this->regionEnds[$start];

        if (! $this->regionNodes[$start] instanceof LanguageAntlersNode) {
            return $this->appendPhpRegion($start, $end, $container);
        }

        $node = $this->makeAntlers($this->regionNodes[$start], $start, $end);
        [$sourceTable, $sourceTableTail] = $this->sourceTablePosition();

        if ($this->tableMode() === TableMode::ColumnGroup
            && $this->arena->name[$this->currentContainer()] === 'colgroup') {
            $this->popOpenElement();
        }

        $containerBeforeReconstruction = $this->currentContainer();
        $reconstructed = false;

        if ($this->shouldReconstructFormattingAroundAntlers()) {
            $this->reconstructActiveFormatting();
            $container = $this->currentContainer();
            $reconstructed = $container !== $containerBeforeReconstruction;
        }

        $fostered = $this->shouldFosterContent();

        if ($fostered) {
            $this->fosterNode($node, $container);
        } else {
            $this->arena->append($container, $node);
            $this->anchorRecoveredSourceNode($node, $container, $sourceTable, $sourceTableTail);
        }

        [$chain, $parentElement] = $this->insertionChain($this->arena->parent($node));

        $this->recordPlacement($this->regionNodes[$start], [
            'bucket' => $this->antlersPlacementBucket(),
            'chain' => $chain,
            'parentElement' => $parentElement,
            'fostered' => $fostered,
            'reconstructed' => $reconstructed,
        ]);

        return $end;
    }

    protected function appendPhpRegion(int $start, int $end, int $container): int
    {
        [$sourceTable, $sourceTableTail] = $this->sourceTablePosition();
        $opaque = $this->arena->opaque(substr($this->source, $start, $end - $start), 'php');
        $this->arena->sourceStart[$opaque] = $start;

        $this->arena->append($container, $opaque);
        $this->anchorRecoveredSourceNode($opaque, $container, $sourceTable, $sourceTableTail);

        return $end;
    }

    protected function makeAntlers(LanguageAntlersNode $node, int $start, int $end): int
    {
        $identity = spl_object_id($node);

        if (! isset($this->antlersNodes[$identity])) {
            $this->antlersNodes[$identity] = $this->arena->antlers(
                $node,
                substr($this->source, $start, $end - $start)
            );
            $this->arena->sourceStart[$this->antlersNodes[$identity]] = $start;
        }

        return $this->antlersNodes[$identity];
    }

    protected function attachEmbeddedRegions(int $element, int $start, int $end, bool $closing = false): void
    {
        if (empty($this->regionStarts)) {
            return;
        }

        foreach ($this->regionsBetween($start, $end) as $region) {
            $embedded = $this->makeAntlers($region['node'], $region['start'], $region['end']);
            $this->arena->embedded[$element][] = $embedded;
            $this->arena->setParent($embedded, $element);
            $this->arena->embeddedOwner[$embedded] = $element;

            [$chain] = $this->insertionChain($this->arena->parent($element));

            $this->recordPlacement($region['node'], [
                'bucket' => 'embedded',
                'owner' => $element,
                'ownerName' => $this->arena->name[$element] ?? null,
                'chain' => $chain,
                'closing' => $closing,
                'markupStart' => $start,
                'markupEnd' => $end,
                'regionStart' => $region['start'],
            ]);
        }
    }

    protected function regionsBetween(int $start, int $end): array
    {
        if (empty($this->regionStarts)) {
            return [];
        }

        $regions = [];
        $index = $this->regionCursor;

        if (($this->regionStarts[$index] ?? PHP_INT_MAX) < $start
            || ($index > 0 && $this->regionStarts[$index - 1] >= $start)) {
            $index = $this->regionIndexAtOrAfter($start);
        }

        for ($count = count($this->regionStarts); $index < $count; $index++) {
            $regionStart = $this->regionStarts[$index];

            if ($regionStart >= $end) {
                break;
            }

            if (! $this->regionNodes[$regionStart] instanceof LanguageAntlersNode) {
                continue;
            }

            $regions[] = [
                'start' => $regionStart,
                'end' => $this->regionEnds[$regionStart],
                'node' => $this->regionNodes[$regionStart],
            ];
        }

        return $regions;
    }

    protected function appendParsedText(string $text, int $offset): void
    {
        if ($text === '') {
            return;
        }

        [$sourceTable, $sourceTableTail] = $this->sourceTablePosition();
        $container = $this->currentContainer();

        if ($this->tableMode() === TableMode::ColumnGroup
            && $this->arena->name[$container] === 'colgroup') {
            $leading = strspn($text, "\t\n\f\r ");

            if ($leading > 0) {
                $this->arena->append($container, $this->arena->text(substr($text, 0, $leading), $offset));
            }

            if ($leading === strlen($text)) {
                return;
            }

            $this->popOpenElement();
            $text = substr($text, $leading);
            $offset += $leading;
        }

        $hasNonWhitespace = strspn($text, "\t\n\f\r ") !== strlen($text);
        $foster = $this->shouldFosterContent() && $hasNonWhitespace;

        if ($this->shouldReconstructFormattingForText($foster)) {
            $this->reconstructActiveFormatting();
        }

        $container = $this->currentContainer();
        $node = $this->arena->text($text, $offset);

        if ($foster) {
            $this->fosterNode($node, $container);
        } else {
            $this->arena->append($container, $node);
            $this->anchorRecoveredSourceNode($node, $container, $sourceTable, $sourceTableTail);
        }
    }

    protected function currentContainer(): int
    {
        if (empty($this->stack)) {
            return 0;
        }

        return $this->stack[count($this->stack) - 1];
    }

    protected function pushOpenElement(int $element): void
    {
        $this->stack[] = $element;
        $this->stackFacts = [];
        $this->countOpenElement($element);
    }

    protected function openStackIndex(int $element): int|false
    {
        for ($index = count($this->stack) - 1; $index >= 0; $index--) {
            if ($this->stack[$index] === $element) {
                return $index;
            }
        }

        return false;
    }

    protected function activeFormattingPosition(int $element): int|false
    {
        for ($index = count($this->activeFormatting) - 1; $index >= 0; $index--) {
            if ($this->activeFormatting[$index] === $element) {
                return $index;
            }
        }

        return false;
    }

    protected function popOpenElement(): void
    {
        $element = array_pop($this->stack);
        $this->stackFacts = [];

        if ($element !== null) {
            $this->releaseOpenElement($element);
        }
    }

    protected function removeOpenElementAt(int $index): void
    {
        [$element] = array_splice($this->stack, $index, 1);
        $this->stackFacts = [];
        $this->releaseOpenElement($element);
    }

    protected function insertOpenElementAt(int $index, int $element): void
    {
        array_splice($this->stack, $index, 0, [$element]);
        $this->stackFacts = [];
        $this->countOpenElement($element);
    }

    protected function replaceOpenElementAt(int $index, int $element): void
    {
        $this->releaseOpenElement($this->stack[$index]);
        $this->stack[$index] = $element;
        $this->stackFacts = [];
        $this->countOpenElement($element);
    }

    protected function sliceOpenStack(?int $length): void
    {
        if ($length === null) {
            return;
        }

        for ($count = count($this->stack); $count > $length; $count--) {
            $this->releaseOpenElement(array_pop($this->stack));
        }

        $this->stackFacts = [];
    }

    protected function resetOpenStack(): void
    {
        $this->stack = [];
        $this->openElementCounts = [];
        $this->stackFacts = [];
    }

    protected function hasOpenElement(string $name): bool
    {
        return isset($this->openElementCounts[$name]);
    }

    protected function countOpenElement(int $element): void
    {
        if ($this->arena->kinds[$element] & (Arena::SVG | Arena::MATHML)) {
            return;
        }

        $name = $this->arena->name[$element];

        if ($name !== null) {
            $this->openElementCounts[$name] = ($this->openElementCounts[$name] ?? 0) + 1;
        }
    }

    protected function releaseOpenElement(int $element): void
    {
        if ($this->arena->kinds[$element] & (Arena::SVG | Arena::MATHML)) {
            return;
        }

        $name = $this->arena->name[$element];

        if ($name === null) {
            return;
        }

        if (($this->openElementCounts[$name] ?? 0) <= 1) {
            unset($this->openElementCounts[$name]);
        } else {
            $this->openElementCounts[$name]--;
        }
    }
}
