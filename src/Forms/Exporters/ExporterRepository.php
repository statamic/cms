<?php

namespace Statamic\Forms\Exporters;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Statamic\Support\Arr;

class ExporterRepository
{
    public function all(): Collection
    {
        return collect(config('statamic.forms.exporters', []))
            ->map(function ($config, $handle) {
                try {
                    $exporter = app($class = Arr::pull($config, 'class'));
                } catch (BindingResolutionException $e) {
                    throw new Exception("Class [$class] does not exist, defined in exporter [$handle].");
                }

                return $exporter->setHandle($handle)->setConfig($config);
            });
    }
}
