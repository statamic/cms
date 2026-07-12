<?php

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
echo $context->emit->raw(<<<ANTLERS
    {{ {$context->variable} }}
    <pre class="language-{{ mode }}">
      <code>{{ code }}</code>
    </pre>
    {{ /{$context->variable} }}
    ANTLERS);
