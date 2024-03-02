<?php

namespace Statamic\Contracts\Data;

interface BulkAugmentable
{
    public function getAugmentationReferenceKey(): string;
}
