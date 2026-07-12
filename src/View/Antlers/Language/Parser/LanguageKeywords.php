<?php

namespace Statamic\View\Antlers\Language\Parser;

class LanguageKeywords
{
    const LogicalAnd = 'and';
    const LogicalNot = 'not';
    const LogicalOr = 'or';
    const LogicalXor = 'xor';

    const ConstTrue = 'true';
    const ConstFalse = 'false';
    const ConstNull = 'null';
    const ArrList = 'list';

    const ScopeAs = 'as';

    public static function isLanguageLogicalKeyword($value)
    {
        if ($value == self::LogicalAnd || $value == self::LogicalNot || $value == self::LogicalOr || $value == self::LogicalXor) {
            return true;
        }

        if (ctype_punct(substr($value, 0))) {
            return true;
        }

        return false;
    }
}
