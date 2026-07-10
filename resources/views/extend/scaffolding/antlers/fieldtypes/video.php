<?php

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
echo $context->emit->raw(<<<ANTLERS
    {{ if {$context->variable} | is_embeddable }}
        <!-- Embeddable video sources, like YouTube and Vimeo -->
        <iframe src="{{ {$context->variable} | embed_url }}"></iframe>
    {{ else }}
        <!-- Other HTML5 video types -->
        <video src="{{ {$context->variable} | embed_url }}"></video>
    {{ /if }}
    ANTLERS);
