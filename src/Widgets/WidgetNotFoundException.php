<?php

namespace Statamic\Widgets;

use Exception;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\Solution;
use Spatie\LaravelIgnition\Support\StringComparator;
use Statamic\Statamic;

class WidgetNotFoundException extends Exception implements ProvidesSolution
{
    protected $widget;

    public function __construct($widget)
    {
        parent::__construct("Widget [{$widget}] not found");

        $this->widget = $widget;
    }

    public function getSolution(): Solution
    {
        $description = ($suggestedWidget = $this->getSuggestedWidget())
            ? "Did you mean `$suggestedWidget`?"
            : 'Are you sure the widget exists?';

        return BaseSolution::create("The {$this->widget} widget was not found.")
            ->setSolutionDescription($description)
            ->setDocumentationLinks([
                'Read the widgets guide' => Statamic::docsUrl('widgets'),
            ]);
    }

    protected function getSuggestedWidget()
    {
        return StringComparator::findClosestMatch(
            app('statamic.widgets')->keys()->all(),
            $this->widget
        );
    }
}
