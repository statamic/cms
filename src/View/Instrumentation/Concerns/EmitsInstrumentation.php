<?php

namespace Statamic\View\Instrumentation\Concerns;

use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Instrumentation\HtmlContext;
use Statamic\View\Instrumentation\InstrumentationState;
use Statamic\View\Instrumentation\TemplateRegion;

/** @internal */
trait EmitsInstrumentation
{
    /** @param  array{regions: TemplateRegion[], elements: array<int, array{insertAt: int, hasAttribute: callable}>}  $analysis */
    protected function emitInstrumentation(string $template, array $analysis): string
    {
        $edits = [];
        $elementMetadata = [];
        $deferredSkips = [];
        $nextId = 0;

        foreach ($analysis['regions'] as $region) {
            $metadata = $this->regionMetadata($region);

            if ($this->nodeFilter !== null
                && ! call_user_func($this->nodeFilter, $metadata, $region)) {
                $this->reportSkip($metadata, $region, self::SKIP_FILTERED);

                continue;
            }

            $emitsComments = $this->commentsEnabled && $region->commentSafe;
            $emitsAttributes = ! empty($this->attributeLayers) && $region->context()->canInstrumentElement();

            if (! $emitsComments && ! $emitsAttributes) {
                $this->reportSkip($metadata, $region, $this->skipReasonFor($region));

                continue;
            }

            $metadata['id'] = ++$nextId;
            $contributed = false;

            if ($emitsComments) {
                [$start, $end] = $this->markersFor($metadata);

                if ($start !== '') {
                    $edits[] = ['start' => $region->byteStart, 'content' => $start, 'type' => 'start', 'nodeStart' => $region->byteStart];
                }

                if ($end !== '') {
                    $edits[] = ['start' => $region->byteEnd, 'content' => $end, 'type' => 'end', 'nodeStart' => $region->byteStart];
                }

                $contributed = $start !== '' || $end !== '';
            }

            if ($emitsAttributes) {
                $elementMetadata[$region->context()->elementStartOffset][] = $metadata;
                $deferredSkips[$region->context()->elementStartOffset][] = [$metadata, $region, $contributed];
                $contributed = true;
            }

            if (! $contributed) {
                $this->reportSkip($metadata, $region, $this->skipReasonFor($region));
            }
        }

        foreach ($elementMetadata as $elementKey => $metadataList) {
            $element = $analysis['elements'][$elementKey] ?? null;

            if ($element === null) {
                $this->reportDeferredSkips($deferredSkips[$elementKey] ?? []);

                continue;
            }

            $attributes = '';

            foreach ($this->attributeLayers as $name => $valueFactory) {
                if (($element['hasAttribute'])($name)) {
                    continue;
                }

                $value = $valueFactory($metadataList);

                if ($value !== null) {
                    $attribute = ' '.$name.'="'.htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'"';
                    $attributes .= $attribute;
                    $edits[] = [
                        'start' => $element['insertAt'],
                        'content' => $attribute,
                        'type' => 'attribute',
                        'nodeStart' => $element['insertAt'],
                        'attributeName' => $name,
                    ];
                }
            }

            if ($attributes === '') {
                $this->reportDeferredSkips($deferredSkips[$elementKey] ?? []);
            }
        }

        if ($this->collectedEdits !== null) {
            $this->collectedEdits = $edits;

            return $template;
        }

        return $this->spliceByteEdits($template, $edits);
    }

    /** @param  array<int, array{0: array<string, mixed>, 1: TemplateRegion, 2: bool}>  $entries */
    protected function reportDeferredSkips(array $entries): void
    {
        foreach ($entries as [$metadata, $region, $alreadyContributed]) {
            if (! $alreadyContributed) {
                $this->reportSkip($metadata, $region, $this->skipReasonFor($region));
            }
        }
    }

    /** @return array<string, mixed> */
    protected function regionMetadata(TemplateRegion $region): array
    {
        $type = 'pair';

        if ($region->type === TemplateRegion::TYPE_SINGLE) {
            $type = 'single';
        }

        $metadata = [
            'type' => $type,
            'engine' => $region->engine,
            'expression' => $region->expression,
            'line' => $region->line,
            'start' => $region->start,
            'end' => $region->end,
        ];

        return $this->appendContextualMetadata($metadata, $region->context());
    }

    /** @param  array<string, mixed>  $metadata */
    protected function reportSkip(array $metadata, TemplateRegion $region, string $reason): void
    {
        if ($this->skipCallback !== null) {
            call_user_func($this->skipCallback, $metadata, $region, $reason);
        }
    }

    protected function skipReasonFor(TemplateRegion $region): string
    {
        $context = $region->context();

        if ($context->kind === HtmlContext::KIND_COMMENT) {
            return self::SKIP_INSIDE_HTML_COMMENT;
        }

        if ($context->kind === HtmlContext::KIND_DOCTYPE) {
            return self::SKIP_INSIDE_DOCTYPE;
        }

        if ($context->kind === HtmlContext::KIND_PHP) {
            return self::SKIP_INSIDE_PHP;
        }

        if ($context->kind === HtmlContext::KIND_ELEMENT_NAME
            || in_array(HtmlContext::DYNAMIC_ELEMENT, $context->elementStack, true)) {
            return self::SKIP_DYNAMIC_MARKUP;
        }

        if ($context->isClosingTag) {
            return self::SKIP_CLOSING_TAG_MARKUP;
        }

        if ($region->insideCharacterReference) {
            return self::SKIP_INSIDE_CHARACTER_REFERENCE;
        }

        if ($this->commentsEnabled && $region->isPair() && $context->isSafeForHtmlComments()) {
            return self::SKIP_CROSS_CONTAINER_PAIR;
        }

        return self::SKIP_NO_STRATEGY;
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    protected function appendContextualMetadata(array $metadata, HtmlContext $context): array
    {
        $metadata['context'] = $context->kind;

        if ($context->elementName !== null) {
            $metadata['element'] = $context->elementName;
        }

        if ($context->attributeName !== null) {
            $metadata['attribute'] = $context->attributeName;
        }

        $view = GlobalRuntimeState::$currentExecutionFile;

        if (is_string($view) && $view !== '' && InstrumentationState::attributesView()) {
            $metadata['view'] = $this->publicViewIdentifier($view);
        }

        return array_merge($metadata, $this->extraMetadata);
    }

    protected function publicViewIdentifier(string $view): string
    {
        $normalized = str_replace('\\', '/', $view);

        if (! preg_match('#^(?:[A-Za-z]:/|/)#', $normalized)) {
            return $normalized;
        }

        $base = rtrim(str_replace('\\', '/', base_path()), '/').'/';
        $comparisonView = $normalized;
        $comparisonBase = $base;

        if (DIRECTORY_SEPARATOR === '\\') {
            $comparisonView = strtolower($comparisonView);
            $comparisonBase = strtolower($comparisonBase);
        }

        if (str_starts_with($comparisonView, $comparisonBase)) {
            return substr($normalized, strlen($base));
        }

        return basename($normalized);
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array{0: string, 1: string}
     */
    protected function markersFor(array $metadata): array
    {
        if ($this->commentFactory !== null) {
            $markers = call_user_func($this->commentFactory, $metadata);

            return [$markers[0] ?? '', $markers[1] ?? ''];
        }

        $json = json_encode($metadata, JSON_INVALID_UTF8_SUBSTITUTE);

        if ($json === false) {
            $json = '{}';
        }

        $encoded = base64_encode($json);
        $id = '';

        if (isset($metadata['id'])) {
            $id = ' '.$metadata['id'];
        }

        return [
            '<!-- '.$this->commentPrefix.':start '.$encoded.' -->',
            '<!-- '.$this->commentPrefix.':end'.$id.' -->',
        ];
    }

    /** @param  array<int, array{start: int, content: string, type: string, nodeStart: int}>  $edits */
    protected function spliceByteEdits(string $template, array $edits): string
    {
        if (empty($edits)) {
            return $template;
        }

        $order = ['end' => 0, 'attribute' => 1, 'start' => 2];

        usort($edits, function ($left, $right) use ($order) {
            if ($left['start'] !== $right['start']) {
                return $left['start'] <=> $right['start'];
            }

            $leftType = $order[$left['type']];
            $rightType = $order[$right['type']];

            if ($leftType !== $rightType) {
                return $leftType <=> $rightType;
            }

            if ($left['type'] === 'end') {
                return ($right['nodeStart'] <=> $left['nodeStart'])
                    ?: (($right['instrumenter'] ?? 0) <=> ($left['instrumenter'] ?? 0));
            }

            return $left['nodeStart'] <=> $right['nodeStart'];
        });

        $result = '';
        $previous = 0;

        foreach ($edits as $edit) {
            $result .= substr($template, $previous, max(0, $edit['start'] - $previous));
            $result .= $edit['content'];
            $previous = max($previous, $edit['start']);
        }

        return $result.substr($template, $previous);
    }
}
