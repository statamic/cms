<?php

namespace Statamic\Forms\JsDrivers;

use Statamic\Fields\Field;
use Statamic\Statamic;

class Alpine extends AbstractJsDriver
{
    protected $scope;

    /**
     * Parse driver options.
     *
     * @param  array  $options
     */
    protected function parseOptions($options)
    {
        $this->scope = $options[0] ?? null;
    }

    /**
     * Add to form html tag attributes.
     *
     * @return array
     */
    public function addToFormAttributes()
    {
        $extraData = $this->params->pull('x-data', []);

        if (is_string($extraData)) {
            $extraData = json_decode($extraData);
        }

        return [
            'x-data' => $this->renderAlpineXData(collect($this->getInitialFormData())->merge($extraData)->all(), $this->scope),
        ];
    }

    /**
     * Add to renderable field view data.
     *
     * @param  \Statamic\Fields\Field  $field
     * @param  array  $data
     * @return array
     */
    public function addToRenderableFieldData($field, $data)
    {
        return [
            'show_field' => $this->renderAlpineShowFieldJs($field, $this->scope),
        ];
    }

    /**
     * Add to renderable field html tag attributes.
     *
     * @param  \Statamic\Fields\Field  $field
     * @return array
     */
    public function addToRenderableFieldAttributes($field)
    {
        $attributes = [];

        if ($field->fieldtype()->hasJsDriverDataBinding()) {
            $attributes['x-model'] = $this->getAlpineXDataKey($field->handle(), $this->scope);
        }

        return $attributes;
    }

    /**
     * Render alpine x-data string for fields, with scope if necessary.
     *
     * @param  array  $xData
     * @param  bool|string  $alpineScope
     * @return string
     */
    protected function renderAlpineXData($xData, $alpineScope)
    {
        if (is_string($alpineScope)) {
            $xData = [
                $alpineScope => $xData,
            ];
        }

        return Statamic::modify($xData)->toJson()->entities();
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
     */
    protected function renderAlpineShowFieldJs(Field $field, ?string $alpineScope): string
    {
        $conditionsObject = Statamic::modify($field->conditions())->toJson()->entities();

        $dataObject = '$data';

        if (is_string($alpineScope)) {
            $dataObject .= ".{$alpineScope}";
        }

        $currentFieldPath = $field->handle();

        return "Statamic.\$conditions.showField({$conditionsObject}, {$dataObject}, '{$currentFieldPath}')";
    }
}
