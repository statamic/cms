<?php

namespace Statamic\View\Html;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Statamic\View\Antlers\Language\Analyzers\Html\Element;

/**
 * @internal
 *
 * @extends Collection<int, Element>
 */
class ElementCollection extends Collection
{
    public function named(string ...$names): static
    {
        return $this->filter(function (Element $element) use ($names) {
            foreach ($names as $name) {
                if ($element->is($name)) {
                    return true;
                }
            }

            return $names === [];
        });
    }

    public function withAttribute(string ...$names): static
    {
        return $this->filter(function (Element $element) use ($names) {
            foreach ($names as $name) {
                if (! $element->hasAttribute($name)) {
                    return false;
                }
            }

            return true;
        });
    }

    public function withAnyAttribute(string ...$names): static
    {
        return $this->filter(function (Element $element) use ($names) {
            foreach ($names as $name) {
                if ($element->hasAttribute($name)) {
                    return true;
                }
            }

            return false;
        });
    }

    public function missingAttribute(string ...$names): static
    {
        return $this->filter(function (Element $element) use ($names) {
            foreach ($names as $name) {
                if (! $element->hasAttribute($name)) {
                    return true;
                }
            }

            return false;
        });
    }

    public function missingAllAttributes(string ...$names): static
    {
        return $this->reject(function (Element $element) use ($names) {
            foreach ($names as $name) {
                if ($element->hasAttribute($name)) {
                    return true;
                }
            }

            return false;
        });
    }

    public function whereAttribute(string $name, ?string $value): static
    {
        return $this->filter(
            fn (Element $element) => $element->hasAttribute($name) && $element->attr($name) === $value
        );
    }

    public function withClass(string $class): static
    {
        return $this->filter(fn (Element $element) => $element->hasClass($class));
    }

    public function missingClass(string $class): static
    {
        return $this->reject(fn (Element $element) => $element->hasClass($class));
    }

    /**
     * @return $this
     *
     * @throws MissingAttributeException
     */
    public function ensureAttribute(string ...$names): static
    {
        if ($names === []) {
            throw new InvalidArgumentException('At least one attribute name is required.');
        }

        $missing = $this->missingAttribute(...$names);

        if ($missing->isNotEmpty()) {
            throw MissingAttributeException::forElements($missing, $names);
        }

        return $this;
    }
}
