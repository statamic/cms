<?php

namespace Statamic\View\Antlers\Language;

use Illuminate\Support\ServiceProvider;
use Statamic\Contracts\View\Antlers\Parser;
use Statamic\View\Antlers\Language\Analyzers\NodeTypeAnalyzer;
use Statamic\View\Antlers\Language\Runtime\Debugging\GlobalDebugManager;
use Statamic\View\Antlers\Language\Runtime\EnvironmentDetails;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\ModifierManager;
use Statamic\View\Antlers\Language\Runtime\RuntimeConfiguration;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;
use Statamic\View\Antlers\Language\Runtime\Tracing\TraceManager;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Statamic\View\Cascade;

class LanguageServiceProvider extends ServiceProvider
{
    public function register()
    {
        GlobalRuntimeState::$environmentId = StringUtilities::uuidv4();

        // Set the debug mode before anything else starts.
        GlobalRuntimeState::$isDebugMode = config('app.debug', false);

        if (GlobalRuntimeState::$isDebugMode) {
            $debugPath = storage_path('antlers/_debug/');

            if (file_exists($debugPath)) {
                GlobalDebugManager::loadDebugConfiguration($debugPath, resource_path());
            }
        }

        $this->app->bind(EnvironmentDetails::class, function ($app) {
            $envDetails = new EnvironmentDetails();

            $envDetails->setTagNames($app->make('statamic.tags')->keys()->all());
            $envDetails->setModifierNames($app->make('statamic.modifiers')->keys()->all());

            NodeTypeAnalyzer::$environmentDetails = $envDetails;

            return $envDetails;
        });

        $this->app->singleton(ModifierManager::class, function ($app) {
            return new ModifierManager();
        });

        $this->app->bind(Parser::class, function ($app) {
            /** @var RuntimeParser $parser */
            $parser = $app->make(RuntimeParser::class)->cascade($app[Cascade::class]);
            $runtimeConfig = new RuntimeConfiguration();

            $isTracingOn = config('statamic.antlers.tracing', false);
            $runtimeConfig->fatalErrorOnUnpairedLoop = config('statamic.antlers.fatalErrorOnUnpairedLoop', false);
            $runtimeConfig->throwErrorOnAccessViolation = config('statamic.antlers.errorOnAccessViolation', false);
            $runtimeConfig->guardedVariablePatterns = config('statamic.antlers.guardedVariables', [
                'config.app.key',
            ]);
            $runtimeConfig->guardedTagPatterns = config('statamic.antlers.guardedTags', []);
            $runtimeConfig->guardedModifiers = config('statamic.antlers.guardedModifiers', []);

            $runtimeConfig->guardedContentVariablePatterns = config('statamic.antlers.guardedContentVariables', []);
            $runtimeConfig->guardedContentTagPatterns = config('statamic.antlers.guardedContentTags', []);
            $runtimeConfig->guardedContentModifiers = config('statamic.antlers.guardedContentModifiers', []);
            $runtimeConfig->allowPhpInUserContent = config('statamic.antlers.allowPhpInContent', false);

            $runtimeConfig->guardedContentVariablePatterns = array_merge(
                $runtimeConfig->guardedVariablePatterns,
                $runtimeConfig->guardedContentVariablePatterns
            );

            $runtimeConfig->guardedContentTagPatterns = array_merge(
                $runtimeConfig->guardedTagPatterns,
                $runtimeConfig->guardedContentTagPatterns
            );

            $runtimeConfig->guardedContentModifiers = array_merge(
                $runtimeConfig->guardedModifiers,
                $runtimeConfig->guardedContentModifiers
            );

            if ($isTracingOn) {
                $traceManager = new TraceManager();
                $tracers = config('statamic.antlers.tracers', []);

                foreach ($tracers as $abstract) {
                    $traceManager->registerTracer($app->make($abstract));
                }

                $runtimeConfig->traceManager = $traceManager;
                $runtimeConfig->isTracingEnabled = true;
            }

            if (GlobalDebugManager::isDebugSessionActive()) {
                if (! $isTracingOn) {
                    $runtimeConfig->traceManager = new TraceManager();
                    $runtimeConfig->isTracingEnabled = true;
                }

                $runtimeConfig->traceManager->registerTracer(GlobalDebugManager::getTimingsTracer());
            }

            $parser->setRuntimeConfiguration($runtimeConfig);

            return $parser;
        });
    }
}
