<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html\Concerns;

use Statamic\View\Antlers\Language\Analyzers\Html\Arena;
use Statamic\View\Antlers\Language\Analyzers\Html\TableMode;

/** @internal */
trait RecoversTables
{
    private const MAX_TABLE_START_RECOVERY_DEPTH = 32;

    protected function prepareTableEnd($name)
    {
        if ($name === 'table') {
            $tableIndex = $this->nearestTableIndex();

            for ($index = count($this->stack) - 1; $tableIndex !== null && $index > $tableIndex; $index--) {
                $element = $this->stack[$index];

                if (! $this->isForeignElement($element)
                    && $this->arena->name[$element] === 'caption') {
                    $this->sliceOpenStack($index);
                    $this->clearActiveFormattingToLastMarker();

                    return;
                }
            }
        }

        $mode = $this->tableMode();

        if ($mode === TableMode::Normal) {
            $mode = $this->tableStructureMode();
        }

        if ($mode === TableMode::Caption && $name === 'table') {
            $this->clearStackBackToTable();
            $this->clearActiveFormattingToLastMarker();

            return;
        }

        if ($mode === TableMode::Cell
            && in_array($name, ['table', 'tbody', 'tfoot', 'thead', 'tr'], true)
            && $this->hasOpenElementInTableScope($name)) {
            $this->closeCurrentTableCell();
        }
    }

    protected function sourceTablePosition()
    {
        $index = $this->nearestTableIndex();

        if ($index === null) {
            return [null, null];
        }

        $table = $this->stack[$index];

        return [$table, $this->arena->lastChild($table)];
    }

    /**
     * Close or synthesize table structure before inserting an opening tag.
     *
     * @return bool Whether the incoming element needs foster parenting.
     */
    protected function prepareTableStart($name)
    {
        // Recovery reprocesses the same tag after changing the stack. Bound that
        // recursion so malformed nesting cannot keep reprocessing indefinitely.
        if (++$this->tableStartDepth > self::MAX_TABLE_START_RECOVERY_DEPTH) {
            $this->tableStartDepth--;

            return false;
        }

        try {
            $mode = $this->tableMode();
            $recoveredThroughContent = false;

            if ($mode === TableMode::Normal
                && (isset(self::$tableStructuralElements[$name])
                    || in_array($name, ['table', 'col', 'tr', 'td', 'th'], true))) {
                $mode = $this->tableStructureMode();
                $recoveredThroughContent = $mode !== TableMode::Normal;
            }

            if ($mode === null || $mode === TableMode::Normal) {
                return false;
            }

            if ($name === 'template') {
                return false;
            }

            if ($recoveredThroughContent) {
                if ($mode === TableMode::Table) {
                    $this->clearStackBackToTable();
                } elseif ($mode === TableMode::TableBody && in_array($name, ['tr', 'td', 'th'], true)) {
                    $this->clearStackBackToTableSection();
                } elseif ($mode === TableMode::Row && ($name === 'td' || $name === 'th')) {
                    $this->clearStackBackToTableRow();
                }
            }

            if ($mode === TableMode::Cell || $mode === TableMode::Caption) {
                if (! isset(self::$tableStructuralElements[$name])
                    && ! in_array($name, ['col', 'tr', 'td', 'th'], true)) {
                    return false;
                }

                if ($mode === TableMode::Cell) {
                    $this->closeCurrentTableCell();
                } else {
                    $this->clearStackBackToTable();
                    $this->clearActiveFormattingToLastMarker();
                }

                return $this->prepareTableStart($name);
            }

            if ($mode === TableMode::Table) {
                if ($name === 'table') {
                    $tableIndex = $this->nearestTableIndex();
                    $this->sliceOpenStack($tableIndex);

                    return false;
                }

                if (isset(self::$allowedInTable[$name])) {
                    return false;
                }

                if ($name === 'col') {
                    $this->appendSyntheticElement('colgroup');

                    return false;
                }

                if ($name === 'tr') {
                    $this->appendSyntheticElement('tbody');

                    return false;
                }

                if ($name === 'td' || $name === 'th') {
                    $this->appendSyntheticElement('tbody');
                    $this->appendSyntheticElement('tr');

                    return false;
                }

                return true;
            }

            if ($mode === TableMode::TableBody) {
                if ($name === 'tr' || $name === 'style' || $name === 'script') {
                    return false;
                }

                if ($name === 'td' || $name === 'th') {
                    $this->appendSyntheticElement('tr');

                    return false;
                }

                if (isset(self::$tableStructuralElements[$name]) || $name === 'table' || $name === 'col') {
                    $this->clearStackBackToTable();

                    return $this->prepareTableStart($name);
                }

                return true;
            }

            if ($mode === TableMode::Row) {
                if (in_array($name, ['td', 'th', 'tr', 'style', 'script'], true)) {
                    return false;
                }

                if (isset(self::$tableStructuralElements[$name]) || $name === 'table' || $name === 'col') {
                    $this->clearStackBackToTable();

                    return $this->prepareTableStart($name);
                }

                return true;
            }

            if ($mode === TableMode::ColumnGroup) {
                if ($name === 'col') {
                    return false;
                }

                array_pop($this->stack);

                return $this->prepareTableStart($name);
            }

            return false;
        } finally {
            $this->tableStartDepth--;
        }
    }

    protected function shouldFosterContent()
    {
        $mode = $this->tableMode();

        if ($mode === TableMode::Table) {
            return true;
        }

        if ($mode === TableMode::TableBody) {
            return true;
        }

        return $mode === TableMode::Row;
    }

    /** The current insertion mode, including intervening ordinary or foreign content. */
    protected function tableMode(): ?TableMode
    {
        $tableIndex = $this->nearestTableIndex();
        $templateIndex = $this->nearestTemplateIndex();

        if ($this->templateIsNearerThanTable($templateIndex, $tableIndex)) {
            return $this->tableModeAbove(
                $templateIndex,
                $this->templateInsertionModes[$this->stack[$templateIndex]] ?? TableMode::Template
            );
        }

        if ($tableIndex === null) {
            return null;
        }

        return $this->tableModeAbove($tableIndex, TableMode::Table);
    }

    protected function tableModeAbove($contextIndex, TableMode $fallback): TableMode
    {
        $context = $this->stack[$contextIndex];

        for ($index = count($this->stack) - 1; $index > $contextIndex; $index--) {
            $element = $this->stack[$index];

            if ($this->isForeignElement($element)) {
                return TableMode::Normal;
            }

            $name = strtolower((string) $this->arena->name[$element]);

            if ($name === 'td' || $name === 'th') {
                return TableMode::Cell;
            }

            if ($name === 'caption') {
                return TableMode::Caption;
            }

            if ($name === 'tr') {
                return TableMode::Row;
            }

            if ($name === 'colgroup') {
                return TableMode::ColumnGroup;
            }

            if (isset(self::$tableSections[$name])) {
                return TableMode::TableBody;
            }

            if ($this->arena->kind($this->stack[$index]) & Arena::SYNTHETIC) {
                if ($this->arena->parent($this->stack[$index]) !== $context) {
                    return TableMode::Normal;
                }
            } else {
                return TableMode::Normal;
            }
        }

        return $this->normalizeTableMode($fallback);
    }

    /** The enclosing table structure, looking past intervening content for recovery. */
    protected function tableStructureMode(): ?TableMode
    {
        $tableIndex = $this->nearestTableIndex();
        $templateIndex = $this->nearestTemplateIndex();

        if ($this->templateIsNearerThanTable($templateIndex, $tableIndex)) {
            return $this->tableStructureModeAbove(
                $templateIndex,
                $this->templateInsertionModes[$this->stack[$templateIndex]] ?? TableMode::Template
            );
        }

        if ($tableIndex === null) {
            return null;
        }

        return $this->tableStructureModeAbove($tableIndex, TableMode::Table);
    }

    protected function templateIsNearerThanTable($templateIndex, $tableIndex)
    {
        return $templateIndex !== null && ($tableIndex === null || $templateIndex > $tableIndex);
    }

    protected function tableStructureModeAbove($contextIndex, TableMode $fallback): TableMode
    {

        for ($index = count($this->stack) - 1; $index > $contextIndex; $index--) {
            $element = $this->stack[$index];

            if ($this->isForeignElement($element)) {
                continue;
            }

            $name = strtolower((string) $this->arena->name[$element]);

            if ($name === 'td' || $name === 'th') {
                return TableMode::Cell;
            }

            if ($name === 'caption') {
                return TableMode::Caption;
            }

            if ($name === 'tr') {
                return TableMode::Row;
            }

            if ($name === 'colgroup') {
                return TableMode::ColumnGroup;
            }

            if (isset(self::$tableSections[$name])) {
                return TableMode::TableBody;
            }
        }

        return $this->normalizeTableMode($fallback);
    }

    protected function normalizeTableMode(TableMode $mode): TableMode
    {
        if ($mode === TableMode::Template) {
            return TableMode::Normal;
        }

        return $mode;
    }

    protected function prepareTemplateInsertionModeForStart($name)
    {
        if ($this->templateInsertionModes === []) {
            return;
        }

        $templateIndex = $this->nearestTemplateIndex();

        if ($templateIndex === null) {
            return;
        }

        $template = $this->stack[$templateIndex];

        if (($this->templateInsertionModes[$template] ?? TableMode::Template) !== TableMode::Template) {
            return;
        }

        if (in_array($name, [
            'base', 'basefont', 'bgsound', 'link', 'meta', 'noframes',
            'script', 'style', 'template', 'title',
        ], true)) {
            return;
        }

        if (in_array($name, ['caption', 'colgroup', 'tbody', 'tfoot', 'thead'], true)) {
            $this->templateInsertionModes[$template] = TableMode::Table;
        } elseif ($name === 'col') {
            $this->templateInsertionModes[$template] = TableMode::ColumnGroup;
        } elseif ($name === 'tr') {
            $this->templateInsertionModes[$template] = TableMode::TableBody;
        } elseif ($name === 'td' || $name === 'th') {
            $this->templateInsertionModes[$template] = TableMode::Row;
        } else {
            $this->templateInsertionModes[$template] = TableMode::Normal;
        }
    }

    protected function nearestTableIndex()
    {
        if (! $this->hasSeenTable) {
            return null;
        }

        for ($index = count($this->stack) - 1; $index >= 0; $index--) {
            $id = $this->stack[$index];

            if (! $this->isForeignElement($id)
                && $this->arena->name[$id] === 'table') {
                return $index;
            }
        }

        $this->hasSeenTable = false;

        return null;
    }

    protected function nearestTemplateIndex()
    {
        if ($this->templateInsertionModes === []) {
            return null;
        }

        for ($index = count($this->stack) - 1; $index >= 0; $index--) {
            $id = $this->stack[$index];

            if (! $this->isForeignElement($id)
                && $this->arena->name[$id] === 'template') {
                return $index;
            }
        }

        $this->templateInsertionModes = [];

        return null;
    }

    protected function clearStackBackToTable()
    {
        $tableIndex = $this->nearestTableIndex();

        if ($tableIndex !== null) {
            $this->sliceOpenStack($tableIndex + 1);
        }
    }

    protected function clearStackBackToTableSection()
    {
        for ($index = count($this->stack) - 1; $index >= 0; $index--) {
            $id = $this->stack[$index];

            if (! $this->isForeignElement($id)
                && isset(self::$tableSections[$this->arena->name[$id]])) {
                $this->sliceOpenStack($index + 1);

                return;
            }
        }
    }

    protected function clearStackBackToTableRow()
    {
        for ($index = count($this->stack) - 1; $index >= 0; $index--) {
            $id = $this->stack[$index];

            if (! $this->isForeignElement($id)
                && $this->arena->name[$id] === 'tr') {
                $this->sliceOpenStack($index + 1);

                return;
            }
        }
    }

    protected function closeCurrentTableCell()
    {
        for ($index = count($this->stack) - 1; $index >= 0; $index--) {
            $id = $this->stack[$index];

            if (! $this->isForeignElement($id)
                && ($this->arena->name[$id] === 'td' || $this->arena->name[$id] === 'th')) {
                $this->sliceOpenStack($index);
                $this->clearActiveFormattingToLastMarker();

                return;
            }
        }
    }

    protected function appendSyntheticElement($name)
    {
        $element = $this->arena->element($name, '', false, 'html', true);
        $this->arena->append($this->currentContainer(), $element);
        $this->stack[] = $element;

        return $element;
    }

    protected function fosterNode($node, $sourceContainer, $character = false)
    {
        $tableIndex = $this->nearestTableIndex();
        $templateIndex = $this->nearestTemplateIndex();

        if ($this->templateIsNearerThanTable($templateIndex, $tableIndex)) {
            $template = $this->stack[$templateIndex];
            $this->arena->append($template, $node);

            if ($sourceContainer !== $template
                && ! $this->anchorAfterSourcePositionTail($node, $sourceContainer)) {
                $this->arena->append($sourceContainer, $this->arena->anchor($node));
            }

            return $node;
        }

        if ($tableIndex === null) {
            $this->arena->append($sourceContainer, $node);

            return $node;
        }

        $table = $this->stack[$tableIndex];
        $parent = $this->arena->parent($table);

        if ($parent === null) {
            $this->arena->append($sourceContainer, $node);

            return $node;
        }

        if ($character) {
            $tableId = $table;
            $reference = $this->fosterCharacterTails[$tableId] ?? $table;

            if ($this->arena->parent($reference) !== $parent) {
                $reference = $table;
            }

            $this->arena->insertAfter($reference, $node);
            $this->fosterCharacterTails[$tableId] = $node;
        } else {
            $this->arena->insertBefore($table, $node);
        }

        $this->arena->append($sourceContainer, $this->arena->anchor($node));

        return $node;
    }

    protected function isTableFosterParent($element)
    {
        if ($this->isForeignElement($element)) {
            return false;
        }

        return in_array(strtolower((string) $this->arena->name[$element]), [
            'table', 'tbody', 'tfoot', 'thead', 'tr',
        ], true);
    }
}
