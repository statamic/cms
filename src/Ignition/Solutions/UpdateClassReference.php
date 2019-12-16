<?php

namespace Statamic\Ignition\Solutions;

use Facade\IgnitionContracts\Solution;

class UpdateClassReference implements Solution
{
    protected $oldClass;
    protected $newClass;
    protected $docs;

    public function __construct($oldClass, $newClass, $docs)
    {
        $this->oldClass = $oldClass;
        $this->newClass = $newClass;
        $this->docs = $docs;
    }

    public function getSolutionTitle(): string
    {
        return 'The class has been moved.';
    }

    public function getSolutionDescription(): string
    {
        return "The reference to `{$this->oldClass}` should be changed to `{$this->newClass}`";
    }

    public function getDocumentationLinks(): array
    {
        return $this->docs;
    }
}
