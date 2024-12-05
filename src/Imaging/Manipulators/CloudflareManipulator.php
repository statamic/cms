<?php

namespace Statamic\Imaging\Manipulators;

class CloudflareManipulator extends Manipulator
{
    public function __construct(private readonly array $config = [])
    {
        //
    }

    public function getAvailableParams(): array
    {
        return [
            'anim',
            'background',
            'blur',
            'border',
            'brightness',
            'compression',
            'contrast',
            'dpr',
            'fit',
            'format',
            'gamma',
            'gravity',
            'height',
            'metadata',
            'onerror',
            'quality',
            'rotate',
            'sharpen',
            'trim',
            'width',
        ];
    }

    public function getUrl(): string
    {
        $base = ($this->config['url'] ?? '').'/cdn-cgi/image/';

        $params = collect($this->getParams())
            ->map(fn ($value, $key) => $key.'='.$value)
            ->implode(',');

        $path = '/test-transforms/gnomes.jpg';

        return $base.$params.$path;
    }

    public function getDataUrl(): string
    {
        // TODO: Implement getDataUrl() method.
    }

    public function getAttributes(): array
    {
        // TODO: Implement getAttributes() method.
    }

    public function addFocalPointParams(float $x, float $y, float $z): self
    {
        $this->addParams([
            'fit' => 'crop',
            'gravity' => $x / 100 .'x'.$y / 100,
        ]);

        return $this;
    }
}
