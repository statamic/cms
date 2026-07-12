<?php

/** @var \Statamic\View\Scaffolding\BladeScaffoldingContext $context */
$rowVar = $context->emit->getCountedVariable('row');
$cellVar = $context->emit->getCountedVariable('cell');

echo $context->emit->raw(<<<BLADE
<table>
  @foreach ({$context->variable} as \$$rowVar)
    <tr>
      @foreach (\${$rowVar}['cells'] as \$$cellVar)
        <td>{{ \$$cellVar }}</td>
      @endforeach
    </tr>
  @endforeach
</table>
BLADE
);

$context->emit->releaseCountedVariable('row');
$context->emit->releaseCountedVariable('cell');
