<?php

namespace Statamic\View\Antlers\Language\Nodes\Paths;

class VariableReference
{
    public $originalContent = '';

    public $normalizedReference = '';

    /**
     * Indicates if the reference is strictly a tag reference.
     *
     * @var bool
     */
    public $isStrictTagReference = false;

    /**
     * Indicates if the reference is strictly a variable.
     *
     * @var bool
     */
    public $isStrictVariableReference = false;

    /**
     * Indicates if the reference is strictly a variable, but default behavior should resume.
     *
     * @var bool
     */
    public $isExplicitVariableReference = false;

    /**
     * Indicates if the variable is a variable variable, or "pointer".
     *
     * @var bool
     */
    public $isVariableVariable = false;

    /**
     * @var PathNode[]|VariableReference[]
     */
    public $pathParts = [];

    public $isFinal = false;

    /**
     * Determines if the provided path is a complex variable path or not.
     *
     * @return bool
     */
    public function isComplex()
    {
        if (empty($this->pathParts)) {
            return false;
        }

        foreach ($this->pathParts as $part) {
            if ($part instanceof PathNode) {
                // Handle the case of variable.5
                if (intval($part->name) == $part->name) {
                    return true;
                }
            } elseif ($part instanceof VariableReference) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return VariableReference
     */
    public function clone()
    {
        $reference = new VariableReference();
        $reference->originalContent = $this->originalContent;
        $reference->normalizedReference = $this->normalizedReference;
        $reference->isStrictVariableReference = $this->isStrictVariableReference;
        $reference->isExplicitVariableReference = $this->isExplicitVariableReference;
        $reference->pathParts = $this->pathParts;
        $reference->isFinal = $this->isFinal;

        return $reference;
    }

    public function implodePaths()
    {
        $stringParts = [];

        foreach ($this->pathParts as $part) {
            if ($part instanceof PathNode) {
                $stringParts[] = $part->name;
            } elseif ($part instanceof VariableReference) {
                $stringParts[] = $part->implodePaths();
            }
        }

        return implode('.', $stringParts);
    }
}
