<?php

namespace Statamic\View\Antlers\Language\Parser\Concerns;

use Illuminate\Support\Str;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\Components\ParameterNode;
use Stillat\BladeParser\Nodes\Components\ParameterType;

trait CompilesBladeComponents
{
    protected function getComponentName(ComponentNode $componentNode)
    {
        $name = $componentNode->name;

        if ($componentNode->tagName == 'slot') {
            $name = Str::after($name, ':');
        }

        return $name;
    }

    protected function compileBladeComponent(ComponentNode $component, string $prefix = '')
    {
        $params = $this->compileParameters($component);
        $name = $prefix.$this->getComponentName($component);

        $tagMethod = 'index';

        if ($component->tagName === 'slot') {
            $tagMethod = 'component_slot';
            $params .= ' component_slot___="'.$name.'"';
        } else {
            $params .= ' component_name___="'.$name.'"';
        }

        if ($component->isClosingTag && ! $component->isSelfClosing) {
            return "{{ /%component_proxy:{$tagMethod} }}";
        }

        if ($component->isSelfClosing) {
            return "{{ %component_proxy:$tagMethod $params /}}";
        }

        $open = "{{ %component_proxy:$tagMethod $params }}";

        $innerContent = $this->compileNodes($component->getNodes());

        $close = "{{ /%component_proxy:$tagMethod }}";

        return $open.$innerContent.$close;
    }

    protected function getParamValue(string $value): string
    {
        return Str::replace('"', '\\"', $value);
    }

    protected function compileTripleEcho(ParameterNode $parameter)
    {
        $content = trim($parameter->content);
        $content = trim(mb_substr($content, 2, mb_strlen($content) - 4));

        if (! Str::contains($content, ' ')) {
            return ':'.$content.'="'.$content.'"';
        }

        $name = trim(Str::before($content, ' '));

        return $name.'="{'.$content.'}"';
    }

    protected function compileParameters(ComponentNode $component): string
    {
        $compiledParameters = [];

        foreach ($component->parameters as $parameter) {
            if ($parameter->type == ParameterType::Parameter) {
                $compiledParameters[] = $parameter->name.'="'.$this->getParamValue($parameter->value).'"';
            } elseif ($parameter->type == ParameterType::Attribute) {
                $compiledParameters[] = $parameter->name.'="'.$parameter->name.'"';
            } elseif ($parameter->type == ParameterType::ShorthandDynamicVariable) {
                $compiledParameters[] = ':'.$parameter->materializedName.'="'.mb_substr($parameter->name, 2).'"';
            } elseif ($parameter->type == ParameterType::DynamicVariable) {
                $compiledParameters[] = ':'.$parameter->materializedName.'="'.$parameter->value.'"';
            } elseif ($parameter->type == ParameterType::InterpolatedValue) {
                $compiledParameters[] = $parameter->name.'="'.$this->getParamValue($parameter->value).'"';
            } elseif ($parameter->type == ParameterType::UnknownEcho) {
                $compiledParameters[] = $this->compileTripleEcho($parameter);
            }
        }

        return implode(' ', $compiledParameters);
    }
}
