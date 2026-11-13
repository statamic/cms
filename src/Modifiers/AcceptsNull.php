<?php

namespace Statamic\Modifiers;

use Attribute;

/**
 * Runs the modifier for null values, which Antlers skips by default.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class AcceptsNull
{
}
