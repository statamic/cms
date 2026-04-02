<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
echo $context->emit->raw(<<<ANTLERS
    @if (Statamic::modify({$context->variable})->isEmbeddable()->fetch())
        <!-- Embeddable video sources, like YouTube and Vimeo -->
        <iframe src="{{ Statamic::modify({$context->variable})->embedUrl() }}"></iframe>
    @else
        <!-- Other HTML5 video types -->
        <video src="{{ Statamic::modify({$context->variable})->embedUrl() }}"></video>
    @endif
    ANTLERS);
