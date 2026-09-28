<?php

/** @var Statamic\View\Scaffolding\AntlersScaffoldingContext $context */
echo $context->emit->raw(<<<ANTLERS
    <table>
      {{ {$context->variable} }}
        <tr>
          {{ cells }}
            <td>{{ value /}}</td>
          {{ /cells }}
        </tr>
      {{ /{$context->variable} }}
    </table>
    ANTLERS);
