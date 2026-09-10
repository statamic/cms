<?php

namespace Statamic\View\Html;

use RuntimeException;
use Statamic\View\Antlers\Language\Analyzers\Html\Element;

/**
 * @internal
 *
 * @phpstan-consistent-constructor
 */
class MissingAttributeException extends RuntimeException
{
    /** @var ElementCollection */
    public $elements;

    /** @var string[] */
    public $attributes;

    /**
     * @param  string[]  $attributes
     * @return static
     */
    public static function forElements(ElementCollection $elements, array $attributes)
    {
        $count = $elements->count();
        $described = $elements->take(3)
            ->map(fn (Element $element) => static::describeElement($element))
            ->implode(', ');
        $more = $count - 3;

        if ($more > 0) {
            $described .= ", and {$more} more";
        }

        if (count($attributes) === 1) {
            $label = 'attribute ['.$attributes[0].']';
        } else {
            $label = 'attributes ['.implode(', ', $attributes).']';
        }

        $subject = 'elements are';

        if ($count === 1) {
            $subject = 'element is';
        }

        $exception = new static(sprintf(
            '%d %s missing the required %s: %s.',
            $count,
            $subject,
            $label,
            $described
        ));

        $exception->elements = $elements;
        $exception->attributes = $attributes;

        return $exception;
    }

    protected static function describeElement(Element $element)
    {
        $name = '<'.($element->name() ?? '{dynamic}').'>';

        if ($element->isSynthetic()) {
            return $name.' (synthesized)';
        }

        $line = $element->sourceLine();

        if ($line === null) {
            return $name;
        }

        return "{$name} on line {$line}";
    }
}
