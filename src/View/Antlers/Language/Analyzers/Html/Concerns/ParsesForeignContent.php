<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html\Concerns;

use Statamic\View\Antlers\Language\Analyzers\Html\Arena;

/** @internal */
trait ParsesForeignContent
{
    protected function isForeignElement($element)
    {
        return (bool) ($this->arena->kind($element) & (Arena::SVG | Arena::MATHML));
    }

    protected function isForeignScopeBoundary($element)
    {
        $name = strtolower((string) $this->arena->name[$element]);
        $namespace = $this->arena->namespace($element);

        if ($namespace === 'svg') {
            return isset(self::$svgHtmlIntegrationPoints[$name]);
        }

        if ($namespace !== 'mathml') {
            return false;
        }

        if ($name === 'annotation-xml') {
            return true;
        }

        return isset(self::$mathMlTextIntegrationPoints[$name]);
    }

    protected function foreignElementEndsScope($element, array $boundaries)
    {
        $name = strtolower((string) $this->arena->name[$element]);

        // These legacy names retain the caller's name-based scope boundary even
        // in foreign content. Other foreign names use namespace-specific rules.
        if (isset($boundaries[$name]) && in_array($name, ['applet', 'marquee', 'object'], true)) {
            return true;
        }

        return $this->isForeignScopeBoundary($element);
    }

    protected function namespaceForForeignChild($name, $container)
    {
        $namespace = $this->arena->namespace($container);

        if ($namespace === 'svg'
            && isset(self::$svgHtmlIntegrationPoints[strtolower((string) $this->arena->name[$container])])) {
            $namespace = 'html';
        }

        if ($namespace === 'mathml') {
            $containerName = strtolower((string) $this->arena->name[$container]);

            if (isset(self::$mathMlTextIntegrationPoints[$containerName])
                && ! in_array($name, ['mglyph', 'malignmark'], true)) {
                $namespace = 'html';
            } elseif ($containerName === 'annotation-xml') {
                if ($name === 'svg') {
                    return 'svg';
                }

                $encoding = strtolower((string) $this->handle($container)->attr('encoding'));

                if ($encoding === 'text/html' || $encoding === 'application/xhtml+xml') {
                    $namespace = 'html';
                }
            }
        }

        if ($namespace === 'html') {
            if ($name === 'svg') {
                return 'svg';
            }

            if ($name === 'math') {
                return 'mathml';
            }
        }

        return $namespace;
    }

    protected function namespaceForStartTag($name, $container)
    {
        if ($container !== 0 && $this->isForeignElement($container)) {
            return $this->namespaceForForeignChild($name, $container);
        }

        if ($name === 'svg') {
            return 'svg';
        }

        if ($name === 'math') {
            return 'mathml';
        }

        return 'html';
    }

    protected function startTagUsesHtmlRules($name, $container)
    {
        if ($container === 0 || ! $this->isForeignElement($container)) {
            return true;
        }

        $containerName = strtolower((string) $this->arena->name[$container]);
        $namespace = $this->arena->namespace($container);

        if ($namespace === 'svg') {
            return isset(self::$svgHtmlIntegrationPoints[$containerName]);
        }

        if (isset(self::$mathMlTextIntegrationPoints[$containerName])) {
            return ! in_array($name, ['mglyph', 'malignmark'], true);
        }

        if ($containerName !== 'annotation-xml') {
            return false;
        }

        if ($name === 'svg') {
            return true;
        }

        return $this->isForeignIntegrationPoint($container);
    }

    protected function characterTokenUsesHtmlRules($container)
    {
        if ($container === 0) {
            return true;
        }

        if (! $this->isForeignElement($container)) {
            return true;
        }

        return $this->isForeignIntegrationPoint($container);
    }

    protected function currentContainerIsForeign()
    {
        $container = $this->currentContainer();

        return $container !== 0 && $this->isForeignElement($container);
    }

    protected function shouldExitForeignContent($name, $markup)
    {
        if (isset(self::$foreignBreakoutElements[$name])) {
            return true;
        }

        if ($name !== 'font') {
            return false;
        }

        return (bool) preg_match('/\s(?:color|face|size)(?=[\x00-\x20=\/>])/i', $markup);
    }

    protected function exitForeignContent()
    {
        while ($this->stack) {
            $container = $this->currentContainer();

            if (! $this->isForeignElement($container)
                || $this->isForeignIntegrationPoint($container)) {
                return;
            }

            $this->sliceOpenStack(count($this->stack) - 1);
        }
    }

    protected function isForeignIntegrationPoint($element)
    {
        $name = strtolower((string) $this->arena->name[$element]);
        $namespace = $this->arena->namespace($element);

        if ($namespace === 'svg') {
            return isset(self::$svgHtmlIntegrationPoints[$name]);
        }

        if (isset(self::$mathMlTextIntegrationPoints[$name])) {
            return true;
        }

        if ($name !== 'annotation-xml') {
            return false;
        }

        $encoding = strtolower((string) $this->handle($element)->attr('encoding'));

        return $encoding === 'text/html' || $encoding === 'application/xhtml+xml';
    }
}
