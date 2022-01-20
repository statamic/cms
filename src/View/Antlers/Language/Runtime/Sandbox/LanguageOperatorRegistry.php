<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox;

class LanguageOperatorRegistry
{
    const ARR_PLUCK = 'pluck';
    const ARR_TAKE = 'take';
    const ARR_SKIP = 'skip';
    const ARR_MAKE = 'arr';
    const ARR_ORDERBY = 'orderby';
    const ARR_GROUPBY = 'groupby';
    const ARR_MERGE = 'merge';
    const QUERY_WHERE = 'where';

    const STRUCT_SWITCH = 'switch';
    const BITWISE_AND = 'bwa';
    const BITWISE_OR = 'bwo';
    const BITWISE_XOR = 'bxor';
    const BITWISE_NOT = 'bnot';
    const BITWISE_SHIFT_LEFT = 'bsl';
    const BITWISE_SHIFT_RIGHT = 'bsr';

    public static $operators = [
        self::ARR_PLUCK => 1,
        self::QUERY_WHERE => 1,
        self::ARR_TAKE => 1,
        self::ARR_SKIP => 1,
        self::ARR_ORDERBY => 1,
        self::ARR_GROUPBY => 1,
        self::ARR_MERGE => 1,
        self::ARR_MAKE => 1,

        self::BITWISE_AND => 1,
        self::BITWISE_OR => 1,
        self::BITWISE_XOR => 1,
        self::BITWISE_NOT => 1,
        self::BITWISE_SHIFT_LEFT => 1,
        self::BITWISE_SHIFT_RIGHT => 1,
        self::STRUCT_SWITCH => 1,
    ];

    public static $getsArgsFromRight = [
        self::BITWISE_NOT => 1,
        self::ARR_MAKE => 1,
    ];

    protected static $cacheRawB = [
        self::QUERY_WHERE => 1,
        self::ARR_ORDERBY => 1,
        self::ARR_GROUPBY => 1,
    ];

    protected static $resultCache = [];
}
