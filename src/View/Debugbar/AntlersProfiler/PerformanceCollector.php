<?php

namespace Statamic\View\Debugbar\AntlersProfiler;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use InvalidArgumentException;
use Statamic\View\Antlers\Language\Runtime\Debugging\GlobalDebugManager;

class PerformanceCollector extends DataCollector implements AssetProvider, Renderable
{
    /**
     * A list of known editor strings.
     *
     * Adapted from Barryvdh\Debugbar\DataCollector\ViewCollector
     *
     * @var string[]
     */
    protected $editors = [
        'sublime' => 'subl://open?url=file://%file&line=%line',
        'textmate' => 'txmt://open?url=file://%file&line=%line',
        'emacs' => 'emacs://open?url=file://%file&line=%line',
        'macvim' => 'mvim://open/?url=file://%file&line=%line',
        'phpstorm' => 'phpstorm://open?file=%file&line=%line',
        'idea' => 'idea://open?file=%file&line=%line',
        'vscode' => 'vscode://file/%file:%line',
        'vscode-insiders' => 'vscode-insiders://file/%file:%line',
        'vscode-remote' => 'vscode://vscode-remote/%file:%line',
        'vscode-insiders-remote' => 'vscode-insiders://vscode-remote/%file:%line',
        'vscodium' => 'vscodium://file/%file:%line',
        'nova' => 'nova://core/open/file?filename=%file&line=%line',
        'xdebug' => 'xdebug://%file@%line',
        'atom' => 'atom://core/open/file?filename=%file&line=%line',
        'espresso' => 'x-espresso://open?filepath=%file&lines=%line',
        'netbeans' => 'netbeans://open/?f=%file:%line',
    ];

    /**
     * Get the editor href for a given file and line, if available.
     *
     * @param  string  $filePath
     * @param  int  $line
     *
     * Adapted from Barryvdh\Debugbar\DataCollector\ViewCollector
     * @return null|string
     *
     * @throws InvalidArgumentException If editor resolver does not return a string
     */
    protected function getEditorHref($filePath, $line)
    {
        if (empty(config('debugbar.editor'))) {
            return null;
        }

        if (empty($this->editors[config('debugbar.editor')])) {
            throw new InvalidArgumentException(
                'Unknown editor identifier: '.config('debugbar.editor').'. Known editors:'.
                implode(', ', array_keys($this->editors))
            );
        }

        $filePath = $this->replaceSitesPath($filePath);

        return str_replace(['%file', '%line'], [$filePath, $line], $this->editors[config('debugbar.editor')]);
    }

    /**
     * Replace remote path
     *
     * Adapted from Barryvdh\Debugbar\DataCollector\ViewCollector
     *
     * @param  string  $filePath
     * @return string
     */
    protected function replaceSitesPath($filePath)
    {
        if (! config('debugbar.remote_sites_path')) {
            return $filePath;
        }

        return str_replace(config('debugbar.remote_sites_path'), config('debugbar.local_sites_path'), $filePath);
    }

    public function getWidgets()
    {
        return [
            'custom_widget' => [
                'widget' => 'PhpDebugBar.Widgets.StatamicAntlersWidget',
                'title' => 'Antlers',
                'map' => 'antlers',
                'icon' => 'code',
                'default' => '{}',
            ],
        ];
    }

    /**
     * Rearranges the output items to make the source view report.
     *
     * Since Antlers layouts are processed last, we need to do
     * some work to inline the output of the other views into
     * the layout. We do this by checking for REPLACED_CONTENT
     * which is inserted when we see {{ template_content }}.
     *
     * @return PerformanceObject[]
     */
    private function makeSourceViewReport(PerformanceTracer $tracer)
    {
        $outputItems = $tracer->getOutputObjects();

        if (! $tracer->getDidFindLayoutTrigger()) {
            return array_values($outputItems);
        }

        $templateContentsPath = $tracer->getPathTriggeringOutput();

        $newItems = [];
        $layout = collect($outputItems)->where(fn (PerformanceObject $item) => $item->path == $templateContentsPath)->all();
        $everythingElse = collect($outputItems)->where(fn (PerformanceObject $item) => $item->path != $templateContentsPath)->all();

        /** @var PerformanceObject $item */
        foreach ($layout as $item) {
            if ($item->escapedBufferOutput == '****REPLACED_CONTENT****') {
                foreach ($everythingElse as $newItem) {
                    $newItems[] = $newItem->toArray(false);
                }
            } else {
                $newItems[] = $item->toArray(false);
            }
        }

        return array_values($newItems);
    }

    public function collect()
    {
        /** @var PerformanceTracer $tracer */
        $tracer = app(PerformanceTracer::class);

        // Update some client-side stuff, like editor links.
        foreach ($tracer->getPerformanceItems() as $item) {
            $item->editorLink = $this->getEditorHref($item->fullPath, $item->line);
        }

        $nodePerformanceItems = [];

        foreach ($tracer->getPerformanceItems() as $item) {
            if ($item->parent != null) {
                continue;
            }

            $nodePerformanceItems[] = $item->toArray();
        }

        $samples = $tracer->getRuntimeSamples();

        return [
            'data' => $tracer->getPerformanceData(),
            'system_samples' => $this->filterSamples($samples),
            'source_samples' => $this->makeSourceViewReport($tracer),
            'total_antlers_nodes' => $tracer->getTotalNodeOperations(),
            'had_active_debug_sessions' => GlobalDebugManager::isDebugSessionActive(),
            'performance_items' => $nodePerformanceItems,
        ];
    }

    private function filterSamples($samples)
    {
        if (count($samples) <= 1000) {
            return $samples;
        }

        $result = [];

        for ($i = 0; $i < count($samples); $i += 5) {
            $result[] = $samples[$i];
        }

        return $this->filterSamples($result);
    }

    public function getName()
    {
        return 'antlers';
    }

    public function getAssets()
    {
        return [
            'js' => [
                __DIR__.'/resources/tabulator.min.js',
                __DIR__.'/resources/chart.js',
                __DIR__.'/resources/widget.js',
            ],
            'css' => [
                __DIR__.'/resources/tabulator.css',
                __DIR__.'/resources/widget.css',
            ],
        ];
    }
}
