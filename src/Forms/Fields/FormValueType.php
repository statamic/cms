<?php

namespace Statamic\Forms\Fields;

/**
 * The shape of the values a form fieldtype stores, used to decide which charts and insights apply.
 *
 * Addons don't add cases. A fieldtype whose values don't fit one of these should return null from
 * valueType() (or the nearest core type if it truly fits), and its charts and insights should
 * override appliesTo(), ideally checking an interface the addon ships so other addons can target it.
 */
enum FormValueType: string
{
    case Number = 'number';
    case Boolean = 'boolean';
    case Choice = 'choice';
    case Choices = 'choices';
    case Ranking = 'ranking';
}
