<?php

namespace Statamic\View\Antlers\Language\Nodes;

use Statamic\View\Antlers\Language\Nodes\Modifiers\ModifierChainNode;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;

abstract class AbstractNode
{
    public $refId = null;

    /**
     * An internal appearance order of the node.
     *
     * @var int
     */
    public $index = 0;

    /**
     * @var AbstractNode|null
     */
    public $parent = null;

    /**
     * The parsed content of the node.
     *
     * @var string
     */
    public $content = '';

    /**
     * @var Position|null
     */
    public $startPosition = null;

    /**
     * @var Position|null
     */
    public $endPosition = null;

    /**
     * @var ModifierChainNode|null
     */
    public $modifierChain = null;

    /**
     * @var AbstractNode|null
     */
    public $originalAbstractNode = null;

    public function __construct()
    {
        $this->refId = StringUtilities::uuidv4();
    }

    public function hasModifiers()
    {
        if ($this->modifierChain == null || empty($this->modifierChain->modifierChain)) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves the inner content of the node.
     *
     * @return string
     */
    public function innerContent()
    {
        return $this->content;
    }

    /**
     * Returns the raw parsed content of the node.
     *
     * @return string
     */
    public function rawContent()
    {
        return $this->content;
    }

    public function getRootRef()
    {
        if ($this->parent != null) {
            return $this->parent->getRootRef();
        }

        return $this->refId;
    }
}
