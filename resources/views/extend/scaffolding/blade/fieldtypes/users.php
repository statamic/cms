<?php

use Statamic\Facades\Blueprint;

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
$maxItems = $context->field->get('max_items');
$userBlueprint = Blueprint::find('user');

if ($maxItems === 1) {
    echo $context->emit->withContext(
        $context->handle,
        fn ($e) => $e->blueprint(
            $userBlueprint,
            fn ($e) => $e->comment('Recursive user fields for '.$context->variable)
        )
    );

    return;
}

echo $context->emit->withCountedVariable('user', function ($userVar) use ($userBlueprint, $context) {
    return $context->emit->forEach(
        $context->handle,
        $userVar,
        content: fn ($e) => $e->blueprint(
            $userBlueprint,
            fn ($e) => $e->comment('Recursive user fields for '.$context->variable)
        )
    );
});
