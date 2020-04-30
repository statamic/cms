<?php

namespace Statamic\Ignition\SolutionProviders;

use Facade\IgnitionContracts\HasSolutionsForThrowable;
use Statamic\Ignition\Solutions\UpdateClassReference;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Throwable;

class UsingOldClass implements HasSolutionsForThrowable
{
    protected $class;

    public function canSolve(Throwable $throwable): bool
    {
        if (! $this->oldClass = $this->getClassFromThrowable($throwable)) {
            return false;
        }

        return Arr::has($this->classes(), $this->oldClass);
    }

    public function getSolutions(Throwable $throwable): array
    {
        $class = $this->classes()[$this->oldClass];

        return [new UpdateClassReference($this->oldClass, $class['class'], $class['docs'] ?? [])];
    }

    protected function getClassFromThrowable($throwable)
    {
        if (! preg_match('/Class \'(.*)\' not found/', $throwable->getMessage(), $matches)) {
            return null;
        }

        return $matches[1];
    }

    protected function classes()
    {
        return [
            'Statamic\Extend\Fieldtype' => [
                'class' => \Statamic\Fields\Fieldtype::class,
                'docs' => ['Fieldtypes Documentation' => Statamic::docsUrl('extending/fieldtypes')],
            ],
            'Statamic\Extend\Modifier' => [
                'class' => \Statamic\Modifiers\Modifier::class,
                'docs' => ['Modifiers Documentation' => Statamic::docsUrl('extending/modifiers')],
            ],
            'Statamic\Extend\Tags' => [
                'class' => \Statamic\Tags\Tags::class,
                'docs' => ['Tags Documentation' => Statamic::docsUrl('extending/tags')],
            ],
            'Statamic\Extend\Widget' => [
                'class' => \Statamic\Widgets\Widget::class,
                'docs' => ['Widgets Documentation' => Statamic::docsUrl('extending/widgets')],
            ],
        ];
    }
}
