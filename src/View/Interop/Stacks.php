<?php

namespace Statamic\View\Interop;

use Illuminate\Support\Facades\Blade;
use Illuminate\View\Factory;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\StackReplacementManager;

class Stacks
{
    public static function register(): void
    {
        Blade::directive('endpush', function () {
            return <<<'PHP'
<?php $___interop_pushed = $__env->stopPush(); Statamic\View\Interop\Stacks::endBladePush($___interop_pushed); ?>
PHP;
        });

        Blade::directive('endpushOnce', function () {
            return <<<'PHP'
<?php $___interop_pushed = $__env->stopPush(); Statamic\View\Interop\Stacks::endBladePush($___interop_pushed); endif; ?>
PHP;
        });

        Blade::directive('endprepend', function () {
            return <<<'PHP'
<?php $___interop_prepend = $__env->stopPrepend(); Statamic\View\Interop\Stacks::endBladePrepend($___interop_prepend); ?>
PHP;
        });

        Blade::directive('endprependOnce', function () {
            return <<<'PHP'
<?php $___interop_prepend = $__env->stopPrepend(); Statamic\View\Interop\Stacks::endBladePrepend($___interop_prepend); endif; ?>
PHP;
        });
    }

    /**
     * @return Factory
     */
    protected static function getBladeEnv()
    {
        return view()->shared('__env');
    }

    protected static function getBladeValue($property, $stack)
    {
        if (GlobalRuntimeState::$renderingLayout) {
            return null;
        }

        // Retrieve value from the Blade environment.
        $values = (fn () => $this->$property)->call(self::getBladeEnv());

        if (! array_key_exists($stack, $values) || empty($values[$stack])) {
            return null;
        }

        // Get the last item from the Blade environment.
        $stackItems = $values[$stack];

        return $stackItems[array_key_last($stackItems)];
    }

    public static function endBladePush($stack): void
    {
        if ($content = self::getBladeValue('pushes', $stack)) {
            StackReplacementManager::pushStack($stack, $content, false, true);
        }
    }

    public static function endBladePrepend($stack): void
    {
        if ($content = self::getBladeValue('prepends', $stack)) {
            StackReplacementManager::prependStack($stack, $content, false, true);
        }
    }

    public static function pushToBladeStack($stack, $content)
    {
        self::getBladeEnv()->startPush($stack, $content);
    }

    public static function prependToBladeStack($stack, $content)
    {
        self::getBladeEnv()->startPrepend($stack, $content);
    }

    public static function restoreStacks()
    {
        $env = self::getBladeEnv();

        // Restore Blade stack contents.
        foreach (StackReplacementManager::getStacks() as $stack => $contents) {
            foreach ($contents as $content) {
                $env->startPush($stack, $content);
            }
        }
    }
}
