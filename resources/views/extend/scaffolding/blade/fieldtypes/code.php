<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
echo $context->emit->raw(<<<BLADE
    <pre class="language-{{ {$context->variable}['mode'] }}">
      <code>{!! {$context->variable}['code'] !!}</code>
    </pre>
    BLADE);
