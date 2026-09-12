<?php

namespace Statamic\View\Instrumentation;

class FieldMarkers extends HtmlInstrumentation
{
    private FieldDomTracer $tracer;

    public function using(FieldDomTracer $tracer): static
    {
        $this->tracer = $tracer;

        $this->attributeLayers['data-statamic-field'] = fn () => null;

        return $this;
    }

    private function isOpaque($region)
    {
        if (
            $region->isPair() &&
            in_array(
                $region->raw()->name?->name,
                ['markdown', 'section', 'push', 'scope'], true
            )
        ) {
            return true;
        }

        return false;
    }

    protected function emitInstrumentation($template, array $analysis)
    {
        $edits = [];
        $elements = [];
        $opaqueUntil = -1;

        foreach ($analysis['regions'] as $region) {
            if ($region->byteStart < $opaqueUntil) {
                continue;
            }

            $opaque = $this->isOpaque($region);
            if ($opaque) {
                $opaqueUntil = $region->byteEnd;
            }

            $element = $region->context()->elementStartOffset;
            $elementSafe = $region->context()->canInstrumentElement() && isset($analysis['elements'][$element]);

            if (! $region->commentSafe && ! $elementSafe) {
                continue;
            }

            $anchor = null;
            if (! $region->commentSafe) {
                if (! isset($elements[$element])) {
                    $facts = $analysis['elements'][$element];
                    if (($facts['hasAttribute'])('data-statamic-field')) {
                        continue;
                    }

                    $elements[$element] = $this->tracer->instruction('element');
                    $edits[] = [
                        'start' => $facts['insertAt'],
                        'remove' => 0,
                        'content' => ' data-statamic-field="'.$this->helper($elements[$element]).'"',
                        'type' => 'attribute',
                        'nodeStart' => $facts['insertAt'],
                        'attributeName' => 'data-statamic-field',
                    ];
                }

                $anchor = $elements[$element];
            }

            $start = $this->tracer->instruction('start', $anchor, $opaque);
            $end = $this->tracer->instruction('end', $start);
            $edits[] = [
                'start' => $region->byteStart,
                'remove' => 0,
                'content' => $this->helper($start),
                'type' => 'start',
                'nodeStart' => $region->byteStart,
            ];
            $edits[] = [
                'start' => $region->byteEnd,
                'remove' => 0,
                'content' => $this->helper($end),
                'type' => 'end',
                'nodeStart' => $region->byteStart,
            ];
        }

        if ($this->collectedEdits !== null) {
            $this->collectedEdits = $edits;

            return $template;
        }

        return $this->spliceByteEdits($template, $edits);
    }

    private function helper(int $id): string
    {
        return '{{ ___internal_field:'.$id.' }}';
    }

    public function instrumentAntlers($template)
    {
        if ($this->tracer->suppressesNestedMarkers()) {
            return $template;
        }

        return parent::instrumentAntlers($template);
    }
}
