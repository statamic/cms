<?php

namespace Tests;

use PHPUnit\Framework\Constraint\Constraint;

class StringEqualsStringIgnoringLineEndings extends Constraint
{
    private string $string;

    public function __construct(string $string)
    {
        $this->string = $this->normalizeLineEndings($string);
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return sprintf(
            'is equal to "%s" ignoring line endings',
            $this->string,
        );
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        return $this->string === $this->normalizeLineEndings((string) $other);
    }

    private function normalizeLineEndings(string $string): string
    {
        return strtr(
            $string,
            [
                "\r\n" => "\n",
                "\r" => "\n",
            ],
        );
    }
}
