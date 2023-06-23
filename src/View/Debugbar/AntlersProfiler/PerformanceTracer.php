<?php

namespace Statamic\View\Debugbar\AntlersProfiler;

use Statamic\Support\Str;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\LiteralNode;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\Tracing\RuntimeTracerContract;

class PerformanceTracer implements RuntimeTracerContract
{
    /**
     * This threshold is used to determine if a template
     * might be doing "too much" and is applied to
     * each individual node. A common scenario
     * this will help detect is loading every
     * entry in a large collection and then
     * using an {{ if }} tag to filter
     * and display a small subset.
     */
    const HotCodeThreshold = 50;

    /**
     * Keeps track of how many Antlers nodes have been processed.
     *
     * @var int
     */
    protected $antlersNodesObserved = 0;

    protected static $conditions = ['if', 'elseif', 'else'];

    /**
     * @var PerformanceObject[]
     */
    protected $nodePerformanceItems = [];
    protected $uniqueFiles = [];
    protected $nodeStarts = [];
    protected $currentDepth = 0;

    protected $totalElapsedTime = 0;

    /**
     * @var PerformanceObject[]
     */
    protected $sourceViewRefIdMapping = [];

    /**
     * @var PerformanceObject[]
     */
    private $sourceViewObjects = [];

    private $triggeredTemplateContent = '';

    private $runtimeSamples = [];

    private $memorySampleBaseline = 0;

    private $firstSampleTime = null;

    public function __construct()
    {
        $this->memorySampleBaseline = memory_get_usage();
    }

    private function normalizePath($path)
    {
        $path = str_replace('\\', '/', $path);

        return str_replace('//', '/', $path);
    }

    private function massageFilePath($path)
    {
        return substr($this->normalizePath($path), strlen(base_path()) + 1);
    }

    public function getOutputObjects()
    {
        return $this->sourceViewObjects;
    }

    public function getTotalNodeOperations()
    {
        return $this->antlersNodesObserved;
    }

    public function getPathTriggeringOutput()
    {
        return $this->triggeredTemplateContent;
    }

    public function getPerformanceData()
    {
        foreach ($this->nodePerformanceItems as $item) {
            if ($item->executionCount > self::HotCodeThreshold) {
                $item->setIsHot();
            }

            $item->clientTotalTime = $item->getTotalExecutionTime();
            $item->clientSelfTime = $item->getSelfExecutionTime();

            if ($this->totalElapsedTime > 0) {
                $item->percentOfExecutionTime = round(($item->totalElapsedTime / $this->totalElapsedTime) * 100, 2);
            }

            $item->clientSelfTimeDisplay = $item->getSelfExecutionTimeDisplay();
            $item->clientTotalTimeDisplay = $item->getTotalExecutionTimeDisplay();

            $item->escapedNodeContent = e($item->nodeContent);
            $item->executionTimeCategory = PerformanceCategory::getCategory($item->totalElapsedTime);
        }

        foreach ($this->sourceViewObjects as $item) {
            $item->escapedNodeContent = e($item->nodeContent);

            if (! $item->isNodeObject) {
                continue;
            }

            // Copy details over.
            if (array_key_exists($item->nodeRefId, $this->nodePerformanceItems)) {
                $reference = $this->nodePerformanceItems[$item->nodeRefId];

                $item->nodeContent = $reference->nodeContent;
                $item->escapedNodeContent = $reference->escapedNodeContent;
                $item->clientSelfTime = $reference->clientSelfTime;
                $item->clientTotalTime = $reference->clientTotalTime;
                $item->clientSelfTimeDisplay = $reference->clientSelfTimeDisplay;
                $item->clientTotalTimeDisplay = $reference->clientTotalTimeDisplay;
                $item->executionTimeCategory = $reference->executionTimeCategory;
                $item->percentOfExecutionTime = $reference->percentOfExecutionTime;
                $item->cumulativeMemorySamples = $reference->cumulativeMemorySamples;
                $item->executionCount = $reference->executionCount;
            }
        }

        // Remove parent references to avoid circular references.
        foreach ($this->nodePerformanceItems as $item) {
            $item->parent = null;
        }

        $files = array_keys($this->uniqueFiles);
        $report = [];

        foreach ($files as $file) {
            $fileItems = collect($this->nodePerformanceItems)->where(fn (PerformanceObject $item) => $item->path == $file);
            $minDepth = $fileItems->min(fn (PerformanceObject $item) => $item->depth);

            $reportItems = $fileItems->where(fn (PerformanceObject $item) => $item->depth == $minDepth)->values()->all();
            $rootItem = new PerformanceObject($this->firstSampleTime);
            $rootItem->path = $file;
            $rootItem->isNodeObject = false;
            $rootItem->children = $reportItems;
            $rootItem->escapedNodeContent = e($rootItem->nodeContent);
            $rootItem->clientSelfTimeDisplay = $rootItem->getTotalExecutionTimeDisplay();
            $rootItem->clientTotalTimeDisplay = $rootItem->getTotalExecutionTimeDisplay();

            if (count($reportItems) > 0) {
                $rootItem->sampleTime = $reportItems[0]->sampleTime;
            }

            /** @var PerformanceObject $item */
            foreach ($reportItems as $item) {
                $rootItem->totalElapsedTime += $item->getTotalExecutionTime();
            }

            $report[] = $rootItem;
        }

        return $report;
    }

    private function sampleEnvironmentData()
    {
        $currentMemory = memory_get_usage() - $this->memorySampleBaseline;

        $sample = new RuntimeSample();
        $sample->time = (microtime(true) * 1000) - $this->firstSampleTime;
        $sample->memory = $currentMemory;
        $sample->antlersNodesProcessed = $this->antlersNodesObserved;

        $this->runtimeSamples[] = $sample;
    }

    /**
     * @return RuntimeSample[]
     */
    public function getRuntimeSamples()
    {
        return $this->runtimeSamples;
    }

    public function getPerformanceItems()
    {
        return $this->nodePerformanceItems;
    }

    public function onEnter(AbstractNode $node)
    {
        if ($node->isVirtual) {
            return;
        }

        if ($this->firstSampleTime == null) {
            $this->firstSampleTime = microtime(true) * 1000;
        }

        $this->sampleEnvironmentData();

        if (GlobalRuntimeState::$currentExecutionFile == null) {
            return;
        }
        if ($node instanceof LiteralNode) {
            return;
        }

        if (! $node instanceof AntlersNode) {
            return;
        }

        if ($node->isComment) {
            return;
        }

        $file = $this->massageFilePath(GlobalRuntimeState::$currentExecutionFile);
        $fullPath = $this->normalizePath(GlobalRuntimeState::$currentExecutionFile);
        $this->antlersNodesObserved += 1;

        if ($node->isClosingTag) {
            $outputClosing = new PerformanceObject($this->firstSampleTime);
            $outputClosing->nodeRefId = $node->refId;

            $outputClosing->bufferOutput = $node->rawStart.$node->content.$node->rawEnd;
            $outputClosing->escapedBufferOutput = e($outputClosing->bufferOutput);
            $outputClosing->path = $file;
            $outputClosing->fullPath = $fullPath;
            $outputClosing->isCloseOutput = true;
            $outputClosing->isNodeObject = true;

            $outputClosing->sourceContent = trim($node->runtimeContent);
            $outputClosing->escapedSourceContent = e($node->runtimeContent);
            $this->sourceViewObjects[] = $outputClosing;
            $this->currentDepth -= 1;

            return;
        }

        $this->uniqueFiles[$file] = 1;

        if ($node->isClosedBy != null) {
            $openNode = new PerformanceObject($this->firstSampleTime);
            $openNode->nodeRefId = $node->refId;
            $openNode->bufferOutput = $node->rawStart.$node->content.$node->rawEnd;
            $openNode->escapedBufferOutput = e($openNode->bufferOutput);
            $openNode->isNodeObject = true;
            $openNode->path = $file;
            $openNode->fullPath = $fullPath;

            $openNode->sourceContent = trim($node->runtimeContent);
            $openNode->escapedSourceContent = e($node->runtimeContent);

            $this->sourceViewRefIdMapping[$node->refId] = $openNode;
            $this->sourceViewObjects[] = $openNode;
        }

        if (! array_key_exists($node->refId, $this->nodePerformanceItems)) {
            $performanceItem = new PerformanceObject($this->firstSampleTime);
            $performanceItem->path = $file;
            $performanceItem->fullPath = $fullPath;

            $performanceItem->isTag = $node->isTagNode;
            $performanceItem->isConditionNode = in_array($node->name->name, self::$conditions);

            $content = $node->content;

            if ($node->originalNode != null) {
                $content = $node->originalNode->content;
            }

            $performanceItem->nodeContent = $node->rawStart.' '.trim($content).' '.$node->rawEnd;
            $performanceItem->sourceContent = trim($node->runtimeContent);
            $performanceItem->escapedSourceContent = e($node->runtimeContent);

            $performanceItem->isNodeObject = true;

            $performanceItem->line = $node->startPosition->line;
            $performanceItem->depth = $this->currentDepth;

            if ($node->parent == null) {
                $performanceItem->depth = 0;
            }

            $this->nodePerformanceItems[$node->refId] = $performanceItem;
        }

        if ($node->parent != null) {
            if (array_key_exists($node->parent->refId, $this->nodePerformanceItems)) {
                $parentPerformanceItem = $this->nodePerformanceItems[$node->parent->refId];

                if (! array_key_exists($node->refId, $parentPerformanceItem->childMap)) {
                    $parentPerformanceItem->childMap[$node->refId] = 1;
                    $parentPerformanceItem->children[] = $this->nodePerformanceItems[$node->refId];
                    $this->nodePerformanceItems[$node->refId]->parent = $parentPerformanceItem;
                }
            }
        }

        $this->nodePerformanceItems[$node->refId]->executionCount += 1;

        if ($node->isClosedBy != null) {
            $this->currentDepth += 1;
        }

        $this->nodeStarts[$node->refId][] = microtime(true) * 1000;
        $this->nodePerformanceItems[$node->refId]->beginSystemSampling();
    }

    public function onExit(AbstractNode $node, $runtimeContent)
    {
        if ($node instanceof AntlersNode && $node->isComment) {
            return;
        }

        if ($node->isVirtual) {
            return;
        }

        if (GlobalRuntimeState::$currentExecutionFile == null) {
            return;
        }

        if ($node instanceof LiteralNode) {
            if (! array_key_exists($node->refId, $this->sourceViewObjects)) {
                $literalObject = new PerformanceObject($this->firstSampleTime);
                $literalObject->bufferOutput = $runtimeContent;
                $literalObject->escapedBufferOutput = e($literalObject->bufferOutput);
                $literalObject->path = $this->massageFilePath(GlobalRuntimeState::$currentExecutionFile);
                $literalObject->fullPath = $this->normalizePath(GlobalRuntimeState::$currentExecutionFile);
                $this->sourceViewObjects[] = $literalObject;
            }

            return;
        }

        if (! $node instanceof AntlersNode) {
            return;
        }

        if (array_key_exists($node->refId, $this->nodePerformanceItems)) {
            $this->nodePerformanceItems[$node->refId]->endSystemSampling();
        }

        $curTime = microtime(true) * 1000;
        if (array_key_exists($node->refId, $this->nodeStarts)) {
            $lastTime = array_pop($this->nodeStarts[$node->refId]);
            $elapsed = $curTime - $lastTime;

            $this->totalElapsedTime += $elapsed;

            if (array_key_exists($node->refId, $this->sourceViewRefIdMapping)) {
                $this->sourceViewRefIdMapping[$node->refId]->incrementElapsedTime($elapsed);
            }

            if (is_bool($runtimeContent) || is_numeric($runtimeContent) || (is_string($runtimeContent)) && ! $node->isTagNode && $node->isClosedBy == null) {
                $outObject = new PerformanceObject($this->firstSampleTime);
                $outObject->nodeRefId = $node->refId;

                $outContent = $runtimeContent;

                if (Str::contains($node->content, 'template_content')) {
                    $outContent = '/*REPLACED_CONTENT*/';
                    $this->triggeredTemplateContent = $this->nodePerformanceItems[$node->refId]->path;
                }

                $outObject->bufferOutput = $outContent;
                $outObject->escapedBufferOutput = e($outObject->bufferOutput);
                $outObject->nodeContent = $this->nodePerformanceItems[$node->refId]->nodeContent;
                $outObject->escapedNodeContent = e($outObject->nodeContent);
                $outObject->isNodeObject = true;
                $outObject->totalElapsedTime = $elapsed;
                $outObject->clientTotalTimeDisplay = $outObject->getTotalExecutionTimeDisplay();
                $outObject->path = $this->nodePerformanceItems[$node->refId]->path;
                $outObject->fullPath = $this->nodePerformanceItems[$node->refId]->fullPath;

                $outObject->sourceContent = trim($node->runtimeContent);
                $outObject->escapedSourceContent = e($node->runtimeContent);

                $this->sourceViewObjects[] = $outObject;
            }

            $this->nodePerformanceItems[$node->refId]->incrementElapsedTime($elapsed);
            $this->nodeStarts[$node->refId][] = $curTime;
        }
    }

    public function onRenderComplete()
    {
    }
}
