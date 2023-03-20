<?php

namespace Statamic\View\Antlers\Language\Nodes;

use Illuminate\Support\Str;

class TagIdentifier
{
    /**
     * The name of the associated node.
     *
     * @var string
     */
    public $name = '';

    /**
     * The compound node name.
     *
     * @var string
     */
    public $compound = '';

    /**
     * The method component of the node's name.
     *
     * @var string
     */
    public $methodPart = '';

    /**
     * The raw content of the node's name.
     *
     * @var string
     */
    public $content = '';

    /**
     * A cached version of the tag's compound variant.
     *
     * @var string|null
     */
    private $cachedCompoundTagName = null;

    public function isCompound()
    {
        return mb_strlen($this->methodPart) > 0;
    }

    public function getMethodName()
    {
        if ($this->methodPart == '') {
            return 'index';
        }

        return $this->methodPart;
    }

    public function getRuntimeMethodName()
    {
        $methodName = Str::camel($this->getMethodName());

        if (Str::contains($methodName, '::')) {
            return 'wildcard';
        }

        return $methodName;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getCompoundTagName()
    {
        if ($this->cachedCompoundTagName == null) {
            $this->cachedCompoundTagName = $this->name.':'.$this->getMethodName();
        }

        return $this->cachedCompoundTagName;
    }
}
