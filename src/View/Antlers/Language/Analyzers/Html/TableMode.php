<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html;

/** @internal */
enum TableMode
{
    case Normal;
    case Table;
    case TableBody;
    case Row;
    case Cell;
    case Caption;
    case ColumnGroup;
    case Template;
}
