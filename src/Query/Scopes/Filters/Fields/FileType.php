<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Illuminate\Support\Arr;
use Statamic\Support\FileTypes;

class FileType extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'operator' => [
                'type' => 'select',
                'placeholder' => __('Select Operator'),
                'options' => [
                    '=' => __('Is'),
                    '<>' => __('Isn\'t'),
                ],
                'default' => '=',
            ],
            'value' => [
                'type' => 'select',
                'options' => [
                    'image' => __('Image'),
                    'image-vector' => __('Vector image'),
                    'image-raster' => __('Raster image'),
                    'video' => __('Video'),
                    'audio' => __('Audio'),
                    'media' => __('Media'),
                    'document' => __('Document'),
                    'archive' => __('Archive'),
                ],
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $extensions = match ($values['value']) {
            'image' => FileTypes::image(),
            'image-vector' => FileTypes::vectorImage(),
            'image-raster' => FileTypes::rasterImage(),
            'video' => FileTypes::video(),
            'audio' => FileTypes::audio(),
            'media' => FileTypes::media(),
            'document' => FileTypes::document(),
            'archive' => FileTypes::archive(),
        };

        match ($values['operator']) {
            '=' => $query->whereIn('extension', $extensions),
            '<>' => $query->whereNotIn('extension', $extensions),
        };
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = $values['operator'];
        $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");
        $value = $values['value'];
        $translatedValue = Arr::get($this->fieldItems(), "value.options.{$value}");

        return $field.' '.strtolower($translatedOperator).' '.$translatedValue;
    }
}
