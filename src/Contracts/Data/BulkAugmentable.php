<?php

namespace Statamic\Contracts\Data;

interface BulkAugmentable
{
    public function getBulkAugmentationReferenceKey(): ?string;
}
