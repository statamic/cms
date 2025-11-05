<?php

namespace Statamic\Tags;

use Illuminate\View\AnonymousComponent;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Compilers\ComponentTagCompiler;
use Illuminate\View\ComponentAttributeBag;
use ReflectionClass;
use Throwable;

class ComponentProxy extends Tags
{
    public static $isolated = true;
    protected static $componentStack = [];

    private function makeComponentTagCompiler(): ComponentTagCompiler
    {
        /** @var BladeCompiler $bladeCompiler */
        $bladeCompiler = app(BladeCompiler::class);

        return new ComponentTagCompiler($bladeCompiler->getClassComponentAliases(), $bladeCompiler->getClassComponentNamespaces(), $bladeCompiler);
    }

    public function index()
    {
        $___obLevel = ob_get_level();

        try {
            $__env = $this->context['__env'] ?? view();

            $__env->incrementRender();

            $componentName = $this->params['component_name___'];
            $tagCompiler = $this->makeComponentTagCompiler();
            $className = $tagCompiler->componentClass($componentName);

            $data = $this->params->except('component_name___')->all();
            $attributes = new ComponentAttributeBag($data);
            $constructorParameters = [];

            $scopeData = $this->context->all();
            $scopeData = array_merge($scopeData, $data);

            $isAnonymous = false;
            $anonymousViewName = $className;

            if (! class_exists($className)) {
                $isAnonymous = true;
                $className = AnonymousComponent::class;
            }

            if ($constructor = (new ReflectionClass($className))->getConstructor()) {
                $constructorParameters = collect($constructor->getParameters())->map->getName()->all();
                $attributes = $attributes->except($constructorParameters);
                $constructorParameters = collect($scopeData)->only($constructorParameters)->all();
            }

            if ($isAnonymous) {
                $constructorParameters = array_merge($constructorParameters, $data, ['view' => $anonymousViewName, 'data' => $data]);
            }

            $component = $className::resolve($constructorParameters + ((array) $attributes->getIterator()));
            $component->withName($componentName);
            $__env->startComponent($component->resolveView(), $component->data());
            $component->withAttributes($attributes->getAttributes());

            if ($this->content) {
                $contextData = array_merge($this->isolatedContext?->all() ?? [], [
                    'component' => $component,
                ]);

                self::$componentStack[] = [$component, $contextData];

                echo $this->parse($contextData);
            }

            $result = $__env->renderComponent();

            $__env->decrementRender();
            $__env->flushStateIfDoneRendering();

            if ($this->content) {
                array_pop(self::$componentStack);
            }

            return ltrim($result);
        } catch (Throwable $e) {
            $this->handleViewException($e, $___obLevel);
        }
    }

    public function componentSlot()
    {
        $___obLevel = ob_get_level();

        try {
            $__env = $this->context['__env'] ?? view();
            $contextData = self::$componentStack[array_key_last(self::$componentStack)][1];

            $slot = $this->params->get('component_slot___');
            $context = $this->params->except('component_slot___')->all();

            $__env->slot($slot, null, $context);

            echo $this->parse($contextData);

            $__env->endSlot();
        } catch (Throwable $e) {
            $this->handleViewException($e, $___obLevel);
        }
    }

    protected function handleViewException(Throwable $e, $obLevel)
    {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }

        throw $e;
    }
}
