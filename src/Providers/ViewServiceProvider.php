<?php

namespace Statamic\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use Statamic\Contracts\View\Antlers\Parser as ParserContract;
use Statamic\Facades\Site;
use Statamic\Statamic;
use Statamic\View\Antlers\Engine;
use Statamic\View\Antlers\Language\Analyzers\NodeTypeAnalyzer;
use Statamic\View\Antlers\Language\Runtime\Debugging\GlobalDebugManager;
use Statamic\View\Antlers\Language\Runtime\EnvironmentDetails;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\ModifierManager;
use Statamic\View\Antlers\Language\Runtime\RuntimeConfiguration;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;
use Statamic\View\Antlers\Language\Runtime\Tracing\TraceManager;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Statamic\View\Antlers\Parser;
use Statamic\View\Blade\AntlersBladePrecompiler;
use Statamic\View\Cascade;
use Statamic\View\Debugbar\AntlersProfiler\PerformanceCollector;
use Statamic\View\Debugbar\AntlersProfiler\PerformanceTracer;
use Statamic\View\Store;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Store::class);

        $this->app->singleton(Cascade::class, function ($app) {
            return new Cascade($app['request'], Site::current());
        });

        $this->registerRuntimeAntlers();
        $this->registerRegexAntlers();

        $this->app->bind(ParserContract::class, function ($app) {
            return config('statamic.antlers.version', 'regex') === 'regex'
                ? $app->make('antlers.regex')
                : $app->make('antlers.runtime');
        });

        $this->app->singleton(Engine::class, function ($app) {
            return new Engine($app['files'], $app[ParserContract::class]);
        });
    }

    private function registerRegexAntlers()
    {
        $this->app->bind('antlers.regex', function ($app) {
            return (new Parser)
                ->callback([Engine::class, 'renderTag'])
                ->cascade($app[Cascade::class]);
        });

        $this->app->bind(Parser::class, fn ($app) => $app['antlers.regex']);
    }

    private function registerRuntimeAntlers()
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

        $this->app->singleton(PerformanceTracer::class, function () {
            return new PerformanceTracer();
        });

        $this->app->bind('antlers.runtime', function ($app) {
            /** @var RuntimeParser $parser */
            $parser = $app->make(RuntimeParser::class)->cascade($app[Cascade::class]);
            $runtimeConfig = new RuntimeConfiguration();

            $isTracingOn = config('statamic.antlers.tracing', false);
            $runtimeConfig->fatalErrorOnUnpairedLoop = config('statamic.antlers.fatalErrorOnUnpairedLoop', false);
            $runtimeConfig->fatalErrorOnStringObject = config('statamic.antlers.fatalErrorOnPrintObjects', false);
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

            if ($this->profilerEnabled()) {
                if (! $isTracingOn) {
                    $runtimeConfig->traceManager = new TraceManager();
                    $runtimeConfig->isTracingEnabled = true;
                }

                $runtimeConfig->traceManager->registerTracer(app(PerformanceTracer::class));
            }

            $parser->isolateRuntimes(GlobalRuntimeState::$requiresRuntimeIsolation)
                ->setRuntimeConfiguration($runtimeConfig);

            return $parser;
        });
    }

    public function registerBladeDirectives()
    {
        Blade::directive('tags', function ($expression) {
            return "<?php extract(\Statamic\View\Blade\TagsDirective::handle($expression)) ?>";
        });
    }

    public function boot()
    {
        ViewFactory::addNamespace('compiled__views', storage_path('framework/views'));

        $this->registerBladeDirectives();

        Blade::precompiler(function ($content) {
            return AntlersBladePrecompiler::compile($content);
        });

        View::macro('withoutExtractions', function () {
            if ($this->engine instanceof Engine) {
                $this->engine->withoutExtractions();
            }

            return $this;
        });

        foreach (Engine::EXTENSIONS as $extension) {
            $this->app['view']->addExtension($extension, 'antlers', function () {
                return $this->app[Engine::class];
            });
        }

        ini_set('pcre.backtrack_limit', config('statamic.system.pcre_backtrack_limit', -1));

        if ($this->profilerEnabled()) {
            debugbar()->addCollector(new PerformanceCollector);
        }
    }

    private function profilerEnabled()
    {
        return debugbar()->isEnabled() && config('statamic.antlers.debugbar', true) && ! Statamic::isCpRoute();
    }
}
