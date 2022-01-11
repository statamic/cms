<?php

namespace Statamic\Forms\JsDrivers;

class Alpine extends AbstractJsDriver
{
    public $scope;

    /**
     * Parse driver options.
     */
    protected function parseOptions()
    {
        $this->scope = $this->options[0] ?? null;
    }

    /**
     * Add to form tag attributes.
     *
     * @param  array  $attrs
     * @param  \Statamic\Forms\Form  $form
     * @return array
     */
    public function addToFormAttributes($attrs, $form)
    {
        return [
            'x-data' => $this->renderAlpineXData($form->blueprint()->fields(), $this->scope),
        ];
    }

    /**
     * Add to renderable field view data.
     *
     * @param  array  $data
     * @param  \Statamic\Fields\Field  $field
     * @return array
     */
    public function addToRenderableFieldData($data, $field)
    {
        return [
            'alpine_data_key' => $this->getAlpineXDataKey($data['handle'], $this->scope),
            'show_field' => $this->renderAlpineShowFieldJs($field->conditions(), $this->scope),
        ];
    }

    /**
     * Render alpine x-data string for fields, with scope if necessary.
     *
     * @param  \Statamic\Fields\Fields  $fields
     * @param  bool|string  $alpineScope
     * @return string
     */
    protected function renderAlpineXData($fields, $alpineScope)
    {
        $oldValues = collect(old());

        $xData = $fields->preProcess()->values()
            ->map(function ($defaultProcessedValue, $handle) use ($oldValues) {
                return $oldValues->has($handle)
                    ? $oldValues->get($handle)
                    : $defaultProcessedValue;
            })
            ->all();

        if (is_string($alpineScope)) {
            $xData = [
                $alpineScope => $xData,
            ];
        }

        return $this->jsonEncodeForHtmlAttribute($xData);
    }

    /**
     * Get alpine x-data key, with scope if necessary.
     *
     * @param  string  $fieldHandle
     * @param  bool|string  $alpineScope
     * @return string
     */
    protected function getAlpineXDataKey($fieldHandle, $alpineScope)
    {
        return is_string($alpineScope)
            ? "{$alpineScope}.{$fieldHandle}"
            : $fieldHandle;
    }

    /**
     * Render alpine `x-if` show field JS logic.
     *
     * @param  array  $conditions
     * @param  string  $alpineScope
     * @return string
     */
    protected function renderAlpineShowFieldJs($conditions, $alpineScope)
    {
        $attrFriendlyConditions = $this->jsonEncodeForHtmlAttribute($conditions);

        $data = '$data';

        if (is_string($alpineScope)) {
            $data .= ".{$alpineScope}";
        }

        return 'Statamic.$conditions.showField('.$attrFriendlyConditions.', '.$data.')';
    }
}
