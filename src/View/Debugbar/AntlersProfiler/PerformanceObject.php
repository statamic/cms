<?php

namespace Statamic\View\Debugbar\AntlersProfiler;

class PerformanceObject
{
    const ReportPrecision = 2;
    public $nodeRefId = '';

    private $cachedChildExecutionTime = null;
    private $cachedTotalExecutionTime = null;

    public $sampleTime = 0;

    public $path = '';
    public $fullPath = '';
    public $editorLink = '';
    /**
     * @var PerformanceObject[]
     */
    public $children = [];
    public $parent = null;
    public $childMap = [];
    public $nodeContent = '';
    public $escapedNodeContent = '';
    public $sourceContent = '';
    public $escapedSourceContent = '';
    public $isNodeObject = false;
    public $executionCount = 0;
    public $totalElapsedTime;
    public $depth = 0;
    public $line = 0;
    public $percentOfExecutionTime = 0;
    public $isConditionNode = false;
    public $bufferOutput = '';
    public $escapedBufferOutput = '';
    public $isCloseOutput = false;
    public $clientTotalTime = 0;
    public $clientTotalTimeDisplay = '';
    public $clientSelfTime = 0;
    public $clientSelfTimeDisplay = '';
    public $isTag = false;
    public $isHot = false;
    public $executionTimeCategory = 0;
    public $initializedMemorySample = 0;
    public $cumulativeMemorySamples = 0;
    protected $memorySampleStart = 0;

    public function __construct($baselineTime)
    {
        $this->sampleTime = (microtime(true) * 1000) - $baselineTime;
        $this->initializedMemorySample = memory_get_usage();
    }

    public function beginSystemSampling()
    {
        $this->memorySampleStart = memory_get_usage();
    }

    public function endSystemSampling()
    {
        $currentMemorySample = memory_get_usage();
        $memoryDifference = $currentMemorySample - $this->memorySampleStart;

        // Prevent de-allocations from causing
        // negative reported memory usage.
        if ($memoryDifference < 0) {
            $memoryDifference = 0;
        }

        $this->cumulativeMemorySamples += $memoryDifference;
    }

    public function setIsHot()
    {
        $this->isHot = true;

        /** @var PerformanceObject|null $parent */
        $parent = $this->parent;

        while ($parent != null) {
            $parent->isHot = true;

            $parent = $parent->parent;
        }
    }

    private function formatMilliseconds($milliseconds)
    {
        if ($milliseconds == null) {
            return 0;
        }

        if ($milliseconds < 0) {
            return round($milliseconds, self::ReportPrecision).' ns';
        } elseif ($milliseconds < 1000) {
            return round($milliseconds, self::ReportPrecision).' ms';
        }

        $seconds = round($milliseconds / 1000, self::ReportPrecision);

        return $seconds.' s';
    }

    public function incrementElapsedTime($elapsedTime)
    {
        $this->totalElapsedTime += $elapsedTime;
    }

    public function getTotalExecutionTimeDisplay()
    {
        return $this->formatMilliseconds($this->getTotalExecutionTime());
    }

    public function getSelfExecutionTimeDisplay()
    {
        return $this->formatMilliseconds($this->getSelfExecutionTime());
    }

    public function getChildExecutionTime()
    {
        if ($this->cachedChildExecutionTime != null) {
            return $this->cachedChildExecutionTime;
        }

        $this->cachedChildExecutionTime = 0;

        foreach ($this->children as $child) {
            $this->cachedChildExecutionTime += $child->getSelfExecutionTime();
        }

        return $this->cachedChildExecutionTime;
    }

    public function getTotalExecutionTime()
    {
        if ($this->cachedTotalExecutionTime != null) {
            return $this->cachedTotalExecutionTime;
        }

        $this->cachedTotalExecutionTime = $this->totalElapsedTime;

        // Conditions won't capture their children, so we need to add them here.
        if ($this->isConditionNode) {
            $this->cachedTotalExecutionTime += $this->getChildExecutionTime();
        }

        return $this->cachedTotalExecutionTime;
    }

    public function getSelfExecutionTime()
    {
        if (count($this->children) == 0) {
            return $this->getTotalExecutionTime();
        }

        return $this->getTotalExecutionTime() - $this->getChildExecutionTime();
    }
}
