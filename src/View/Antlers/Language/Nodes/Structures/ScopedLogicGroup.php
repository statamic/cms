<?php

namespace Statamic\View\Antlers\Language\Nodes\Structures;

use Statamic\View\Antlers\Language\Nodes\VariableNode;

class ScopedLogicGroup extends LogicGroup
{
    /**
     * @var VariableNode|null
     */
    public $scope = null;

    public function extract()
    {
        $scopeName = null;

        if ($this->scope != null) {
            $scopeName = $this->scope->name;
        }

        /** @var SemanticGroup $semanticWrapper */
        $semanticWrapper = $this->nodes[0];

        return [
            $scopeName,
            $semanticWrapper->nodes,
        ];
    }
}
