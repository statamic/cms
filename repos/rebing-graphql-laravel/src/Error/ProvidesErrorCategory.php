<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Error;

interface ProvidesErrorCategory
{
    public function getCategory(): string;
}
