<?php

namespace Statamic\Imaging\Manipulators;

use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Imaging\Manipulator as Contract;
use Statamic\Support\Str;

abstract class Manipulator implements Contract
{
    protected string|Asset $source;
    protected array $params;

    public function setSource(string|Asset $source): Contract
    {
        $this->source = $source;

        return $this;
    }

    public function setParams(array $params): Contract
    {
        $this->params = $params;

        return $this;
    }

    protected function getSourceType(): SourceType
    {
        if ($this->source instanceof Asset) {
            return SourceType::Asset;
        } elseif (Str::startsWith($this->source, ['http://', 'https://'])) {
            return SourceType::Url;
        } elseif (Str::contains($this->source, '::')) {
            return SourceType::AssetId;
        }

        return SourceType::Path;
    }
}
