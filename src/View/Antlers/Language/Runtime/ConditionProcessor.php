<?php

namespace Statamic\View\Antlers\Language\Runtime;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\Conditions\ConditionNode;
use Statamic\View\Antlers\Language\Parser\LanguageParser;
use Statamic\View\Antlers\Language\Runtime\Sandbox\Environment;

class ConditionProcessor
{
    protected static $branchCache = [];

    /**
     * @var NodeProcessor|null
     */
    protected $processor = null;

    /**
     * Sets the node processor instance.
     *
     * @param  NodeProcessor|null  $nodeProcessor  The NodeProcessor instance.
     * @return $this
     */
    public function setProcessor($nodeProcessor)
    {
        $this->processor = $nodeProcessor;

        return $this;
    }

    public function process(ConditionNode $node, $data)
    {
        foreach ($node->logicBranches as $branch) {
            if ($branch->head->name->name == 'else') {
                return $branch;
            } else {
                $parser = new LanguageParser();
                $environment = new Environment();
                $environment->setProcessor($this->processor);
                $dataToUse = $data;
                $interpolationReplacements = [];

                if (! empty($branch->head->processedInterpolationRegions)) {
                    $this->processor->registerInterpolations($branch->head);
                }

                if (! empty($branch->head->interpolationRegions)) {
                    /**
                     * @var string $varKey
                     * @var AbstractNode[] $interpolationRegion
                     */
                    foreach ($branch->head->processedInterpolationRegions as $varKey => $interpolationRegion) {
                        $interpolationResult = $this->processor->cloneProcessor()->setData($data)->render($interpolationRegion);

                        $dataToUse[$varKey] = $interpolationResult;
                        $interpolationReplacements[$varKey] = $interpolationResult;
                    }
                }

                $environment->setInterpolationReplacements($interpolationReplacements);
                $environment->setData($dataToUse);

                if (array_key_exists($branch->head->content, self::$branchCache) == false) {
                    self::$branchCache[$branch->head->content] = $parser->parse($branch->head->runtimeNodes);
                }

                $result = $environment->evaluateBool(self::$branchCache[$branch->head->content]);

                if ($result === true) {
                    return $branch;
                }
            }
        }

        return null;
    }
}
