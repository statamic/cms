<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html\Concerns;

use Statamic\View\Antlers\Language\Analyzers\Html\Arena;

/** @internal */
trait AdoptsFormattingElements
{
    private const MAX_FORMATTING_ADOPTION_PASSES = 8;

    private const MAX_FORMATTING_INNER_RECONSTRUCTIONS = 3;

    protected function closeFormattingElement(string $name, ?string $closingMarkup = null, ?int $start = null, ?int $end = null): bool
    {
        $activeIndex = $this->activeFormattingIndex($name);

        if ($activeIndex === null) {
            return false;
        }

        $formatting = $this->activeFormatting[$activeIndex];
        $match = $this->openStackIndex($formatting);

        if ($match === false) {
            $this->removeActiveFormattingAt($activeIndex);

            if ($closingMarkup !== null) {
                $this->appendOpaqueMarkup($start, $end, $closingMarkup);
            }

            return true;
        }

        if (! $this->stackIndexIsInScope($match, self::$scopeBoundaries)) {
            if ($closingMarkup !== null) {
                $this->appendOpaqueMarkup($start, $end, $closingMarkup);
            }

            return true;
        }

        if ($this->adoptFormattingAroundBlocks($match, $formatting, $closingMarkup, $start, $end)) {
            return true;
        }

        if ($closingMarkup !== null) {
            $this->attachEmbeddedRegions($formatting, $start, $end, true);
            $this->setElementClosingMarkup($formatting, $closingMarkup, $start);
        }

        $this->sliceOpenStack($match);
        $this->removeActiveFormattingAt($activeIndex);

        return true;
    }

    protected function adoptFormattingAroundBlocks(int $match, int $formatting, ?string $closingMarkup, ?int $start, ?int $end): bool
    {
        $transformed = false;
        $closingOwner = null;

        for ($outer = 0; $outer < self::MAX_FORMATTING_ADOPTION_PASSES; $outer++) {
            $activeIndex = $this->activeFormattingIndex($this->arena->name[$formatting]);

            if ($activeIndex === null) {
                break;
            }

            $formatting = $this->activeFormatting[$activeIndex];
            $match = $this->openStackIndex($formatting);

            if ($match === false) {
                $this->removeActiveFormattingAt($activeIndex);
                break;
            }

            $furthestIndex = $this->furthestFormattingBoundaryIndex($match);

            if ($furthestIndex === null) {
                if (! $transformed) {
                    return false;
                }

                $closingOwner = $formatting;
                $this->sliceOpenStack($match);
                $this->removeActiveFormattingAt($activeIndex);
                break;
            }

            $transformed = true;
            $furthest = $this->stack[$furthestIndex];
            $commonAncestor = 0;

            if ($match !== 0) {
                $commonAncestor = $this->stack[$match - 1];
            }

            $bookmark = $activeIndex;
            $lastNode = $furthest;
            $inner = 0;

            for ($index = $furthestIndex - 1; $index > $match; $index--) {
                $inner++;
                $node = $this->stack[$index];
                $nodeActiveIndex = $this->activeFormattingPosition($node);

                if ($inner > self::MAX_FORMATTING_INNER_RECONSTRUCTIONS && $nodeActiveIndex !== false) {
                    $this->removeActiveFormattingAt($nodeActiveIndex);
                    $nodeActiveIndex = false;
                }

                if ($nodeActiveIndex === false) {
                    $this->removeOpenElementAt($index);
                    $furthestIndex--;

                    continue;
                }

                $copy = $this->reconstructedCopy($node);
                $this->activeFormatting[$nodeActiveIndex] = $copy;
                $this->replaceOpenElementAt($index, $copy);

                if ($lastNode === $furthest) {
                    $bookmark = $nodeActiveIndex + 1;
                }

                $this->anchorArenaNodeForMove($lastNode);
                $this->arena->append($copy, $lastNode);
                $lastNode = $copy;
            }

            $this->anchorArenaNodeForMove($lastNode);

            if ($this->arena->parent($formatting) === $commonAncestor) {
                $this->arena->insertAfter($formatting, $lastNode);
            } elseif ($this->isTableFosterParent($commonAncestor)) {
                $tableIndex = $this->nearestTableIndex();
                $table = null;

                if ($tableIndex !== null) {
                    $table = $this->stack[$tableIndex];
                }

                if ($table !== null && $this->arena->parent($table) !== null) {
                    $this->arena->insertBefore($table, $lastNode);
                } else {
                    $this->arena->append($commonAncestor, $lastNode);
                }
            } else {
                $this->arena->append($commonAncestor, $lastNode);
            }

            $newFormatting = $this->reconstructedCopy($formatting);

            $moving = [];

            for ($child = $this->arena->firstChild($furthest); $child !== null; $child = $this->arena->next($child)) {
                if (($this->arena->kinds[$child] & Arena::KIND_MASK) !== Arena::SOURCE_ANCHOR) {
                    $moving[] = $child;
                }
            }

            foreach ($moving as $child) {
                $this->anchorArenaNodeForMove($child);
                $this->arena->append($newFormatting, $child);
            }

            $this->arena->append($furthest, $newFormatting);
            $closingOwner = $newFormatting;

            $formattingActiveIndex = $this->activeFormattingPosition($formatting);

            if ($formattingActiveIndex !== false) {
                $this->removeActiveFormattingAt($formattingActiveIndex);

                if ($formattingActiveIndex < $bookmark) {
                    $bookmark--;
                }
            }

            $this->insertActiveFormattingAt($bookmark, $newFormatting);
            $formattingStackIndex = $this->openStackIndex($formatting);

            if ($formattingStackIndex !== false) {
                $this->removeOpenElementAt($formattingStackIndex);
            }

            $furthestStackIndex = $this->openStackIndex($furthest);

            if ($furthestStackIndex === false) {
                $this->pushOpenElement($newFormatting);
            } else {
                $this->insertOpenElementAt($furthestStackIndex + 1, $newFormatting);
            }
        }

        if ($transformed) {
            $this->insertionChains = [];
        }

        if (! $transformed || $closingOwner === null) {
            return false;
        }

        if ($closingMarkup !== null) {
            if ($outer === self::MAX_FORMATTING_ADOPTION_PASSES) {
                $this->appendOpaqueMarkup($start, $end, $closingMarkup);
            } else {
                $this->setElementClosingMarkup($closingOwner, $closingMarkup, $start);
                $this->attachEmbeddedRegions($closingOwner, $start, $end, true);
            }
        }

        return true;
    }

    protected function reconstructActiveFormatting(): void
    {
        $count = count($this->activeFormatting);

        if ($count === 0
            || $this->activeFormatting[$count - 1] === null
            || $this->openStackIndex($this->activeFormatting[$count - 1]) !== false) {
            return;
        }

        $index = $count - 1;

        while ($index > 0
            && $this->activeFormatting[$index - 1] !== null
            && $this->openStackIndex($this->activeFormatting[$index - 1]) === false) {
            $index--;
        }

        for (; $index < $count; $index++) {
            $copy = $this->reconstructedCopy($this->activeFormatting[$index]);
            $container = $this->currentContainer();

            if ($this->shouldFosterContent()) {
                $this->fosterNode($copy, $container);
            } else {
                $this->arena->append($container, $copy);
            }

            $this->pushOpenElement($copy);
            $this->activeFormatting[$index] = $copy;
        }
    }

    protected function activeFormattingIndex(string $name): ?int
    {
        for ($index = count($this->activeFormatting) - 1; $index >= 0; $index--) {
            if ($this->activeFormatting[$index] === null) {
                return null;
            }

            if (strcasecmp((string) $this->arena->name[$this->activeFormatting[$index]], $name) === 0) {
                return $index;
            }
        }

        return null;
    }

    protected function stackIndexIsInScope(int $targetIndex, array $boundaries): bool
    {
        for ($index = count($this->stack) - 1; $index >= 0; $index--) {
            if ($index === $targetIndex) {
                return true;
            }

            $element = $this->stack[$index];

            if ($this->isForeignElement($element)) {
                if ($this->foreignElementEndsScope($element, $boundaries)) {
                    return false;
                }

                continue;
            }

            if (isset($boundaries[$this->arena->name[$element] ?? ''])) {
                return false;
            }
        }

        return false;
    }

    protected function furthestFormattingBoundaryIndex(int $formattingIndex): ?int
    {
        for ($index = $formattingIndex + 1, $count = count($this->stack); $index < $count; $index++) {
            $candidate = $this->stack[$index];
            $name = $this->arena->name[$candidate] ?? '';

            if ($this->isFormattingBoundary($candidate, $name)) {
                return $index;
            }
        }

        return null;
    }

    protected function isFormattingBoundary(int $candidate, string $name): bool
    {
        if ($this->isForeignElement($candidate)) {
            return $this->isForeignScopeBoundary($candidate);
        }

        return isset(self::$formattingBreakStarts[$name]);
    }

    protected function pushActiveFormattingElement(int $element): void
    {
        $signature = $this->formattingSignature($element);

        if (($this->activeSignatureCounts[$signature] ?? 0) >= 3) {
            $equivalent = [];

            for ($index = count($this->activeFormatting) - 1; $index >= 0; $index--) {
                $candidate = $this->activeFormatting[$index];

                if ($candidate === null) {
                    break;
                }

                if ($this->formattingSignature($candidate) === $signature) {
                    $equivalent[] = $index;

                    if (count($equivalent) === 3) {
                        break;
                    }
                }
            }

            if (count($equivalent) >= 3) {
                $this->removeActiveFormattingAt($equivalent[count($equivalent) - 1]);
            }
        }

        $this->activeFormatting[] = $element;
        $this->countActiveFormatting($element, 1);
    }

    protected function pushActiveFormattingMarker(): void
    {
        $this->activeFormatting[] = null;
        $this->activeSignatureCountStack[] = $this->activeSignatureCounts;
        $this->activeSignatureCounts = [];
    }

    protected function removeActiveFormattingAt(int $index): void
    {
        [$element] = array_splice($this->activeFormatting, $index, 1);

        if ($element !== null) {
            $this->countActiveFormatting($element, -1);
        }
    }

    protected function insertActiveFormattingAt(int $index, int $element): void
    {
        array_splice($this->activeFormatting, $index, 0, [$element]);
        $this->countActiveFormatting($element, 1);
    }

    protected function countActiveFormatting(int $element, int $delta): void
    {
        $signature = $this->formattingSignature($element);
        $count = ($this->activeSignatureCounts[$signature] ?? 0) + $delta;

        if ($count > 0) {
            $this->activeSignatureCounts[$signature] = $count;
        } else {
            unset($this->activeSignatureCounts[$signature]);
        }
    }

    protected function formattingSignature(int $element): string
    {
        if (isset($this->formattingSignatures[$element])) {
            return $this->formattingSignatures[$element];
        }

        $markup = $this->arena->value[$element];

        if (strpos($markup, '{{') !== false) {
            $attributes = $markup;
        } else {
            $attributes = $this->handle($element)->attributes();
            ksort($attributes);
            $attributes = serialize($attributes);
        }

        return $this->formattingSignatures[$element] = $this->arena->namespace($element)
            .'|'.$this->arena->name[$element]
            .'|'.$attributes;
    }

    protected function clearActiveFormattingToLastMarker(): void
    {
        for ($index = count($this->activeFormatting) - 1; $index >= 0; $index--) {
            if ($this->activeFormatting[$index] === null) {
                $this->activeFormatting = array_slice($this->activeFormatting, 0, $index);
                $this->activeSignatureCounts = array_pop($this->activeSignatureCountStack) ?? [];

                return;
            }
        }
    }

    protected function reconstructedCopy(int $element): int
    {
        $copy = $this->arena->element(
            $this->arena->name[$element],
            $this->arena->value[$element],
            false,
            $this->arena->namespace($element),
            true
        );

        if (isset($this->arena->sourceStart[$element])) {
            $this->arena->sourceStart[$copy] = $this->arena->sourceStart[$element];
        }

        if (isset($this->formattingSignatures[$element])) {
            $this->formattingSignatures[$copy] = $this->formattingSignatures[$element];
        }

        return $copy;
    }
}
