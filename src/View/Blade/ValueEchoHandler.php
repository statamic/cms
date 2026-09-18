<?php

namespace Statamic\View\Blade;

use Statamic\Facades\Antlers;
use Statamic\Facades\Cascade;
use Statamic\Fields\Value;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;

class ValueEchoHandler
{
    public static function handle(Value $value): mixed
    {
        if (! $value->shouldParseAntlers()) {
            return $value;
        }

        $data = Cascade::toArray() ?: Cascade::hydrate()->toArray();

        $wasEvaluatingUserData = GlobalRuntimeState::$isEvaluatingUserData;
        GlobalRuntimeState::$isEvaluatingUserData = true;
        GlobalRuntimeState::$userContentEvalState = [$value, null];

        try {
            return $value->antlersValue(Antlers::parser(), $data);
        } finally {
            GlobalRuntimeState::$userContentEvalState = null;
            GlobalRuntimeState::$isEvaluatingUserData = $wasEvaluatingUserData;
        }
    }
}
