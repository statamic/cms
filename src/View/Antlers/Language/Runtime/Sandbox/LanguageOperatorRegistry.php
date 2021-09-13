<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox;

class LanguageOperatorRegistry
{
    const STR_STARTS_WITH = 'startswith';
    const STR_ENDS_WITH = 'endswith';
    const STR_IS = 'str_is';
    const STR_IS_ASCII = 'is_ascii';
    const STR_ASCII = 'str_ascii';
    const STR_IS_UUID = 'is_uuid';
    const STR_IS_URL = 'is_url';
    const STR_FINISH = 'str_finish';
    const STR_CONTAINS = 'str_contains';
    const STR_AFTER = 'str_after';
    const STR_LOWER = 'str_lower';
    const STR_UPPER = 'str_upper';
    const STR_UCFIRST = 'str_ucfirst';
    const STR_LENGTH = 'str_len';
    const STR_AFTER_LAST = 'str_after_last';
    const STR_BEFORE = 'str_before';
    const STR_BEFORE_LAST = 'str_before_last';
    const STR_CONTAINS_ALL = 'str_contains_all';
    const STR_CAMEL = 'str_camel';
    const STR_SNAKE = 'str_snake';
    const STR_WORD_COUNT = 'str_word_count';
    const STR_STUDLY = 'str_studly';
    const STR_KEBAB = 'str_kebab';
    const STR_TITLE = 'str_title';
    const ARR_HAS = 'arr_has';
    const ARR_CONTAINS = 'arr_contains';
    const ARR_CONTAINS_ANY = 'contains_any';
    const ARR_SORT = 'arr_sort';
    const ARR_WRAP = 'arr_wrap';
    const ARR_RECURSIVE_SORT = 'arr_sort_recursive';
    const ARR_HAS_ANY = 'has_any';
    const ARR_PLUCK = 'pluck';
    const ARR_GET = 'get';
    const ARR_TAKE = 'take';
    const ARR_MAKE = 'arr';
    const ARR_ORDERBY = 'orderby';
    const ARR_GROUPBY = 'groupby';
    const ARR_MERGE = 'merge';
    const ARR_CONCAT = 'concat';
    const ARR_PLUCK_INTO = 'pluck_into';
    const QUERY_WHERE = 'where';
    const DATA_GET = 'data_get';

    const STRUCT_SWITCH = 'switch';
    const BITWISE_AND = 'bwa';
    const BITWISE_OR = 'bwo';
    const BITWISE_XOR = 'bxor';
    const BITWISE_NOT = 'bnot';
    const BITWISE_SHIFT_LEFT = 'bsl';
    const BITWISE_SHIFT_RIGHT = 'bsr';

    public static $operators = [
        self::STR_STARTS_WITH => 1,
        self::STR_ENDS_WITH => 1,
        self::STR_IS => 1,
        self::STR_IS_ASCII => 1,
        self::STR_IS_UUID => 1,
        self::STR_IS_URL => 1,
        self::STR_CAMEL => 1,
        self::STR_CONTAINS => 1,
        self::STR_CONTAINS_ALL => 1,
        self::STR_ASCII => 1,
        self::ARR_HAS => 1,
        self::ARR_HAS_ANY => 1,
        self::ARR_PLUCK => 1,
        self::ARR_GET => 1,
        self::ARR_SORT => 1,
        self::ARR_RECURSIVE_SORT => 1,
        self::ARR_WRAP => 1,
        self::DATA_GET => 1,
        self::STR_AFTER => 1,
        self::STR_AFTER_LAST => 1,
        self::STR_BEFORE => 1,
        self::STR_BEFORE_LAST => 1,
        self::STR_FINISH => 1,
        self::STR_KEBAB => 1,
        self::STR_LENGTH => 1,
        self::STR_UPPER => 1,
        self::STR_LOWER => 1,
        self::STR_SNAKE => 1,
        self::STR_STUDLY => 1,
        self::STR_TITLE => 1,
        self::STR_UCFIRST => 1,
        self::STR_WORD_COUNT => 1,
        self::ARR_CONTAINS => 1,
        self::ARR_CONTAINS_ANY => 1,
        self::QUERY_WHERE => 1,
        self::ARR_TAKE => 1,
        self::ARR_ORDERBY => 1,
        self::ARR_GROUPBY => 1,
        self::ARR_MERGE => 1,
        self::ARR_CONCAT => 1,
        self::ARR_PLUCK_INTO => 1,
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
        self::STR_IS_ASCII => 1,
        self::STR_ASCII => 1,
        self::STR_IS_UUID => 1,
        self::STR_IS_URL => 1,
        self::ARR_SORT => 1,
        self::ARR_RECURSIVE_SORT => 1,
        self::ARR_WRAP => 1,
        self::STR_CAMEL => 1,
        self::STR_KEBAB => 1,
        self::STR_LENGTH => 1,
        self::STR_UPPER => 1,
        self::STR_LOWER => 1,
        self::STR_SNAKE => 1,
        self::STR_STUDLY => 1,
        self::STR_TITLE => 1,
        self::STR_UCFIRST => 1,
        self::STR_WORD_COUNT => 1,
        self::BITWISE_NOT => 1,
        self::ARR_MAKE => 1,
    ];

    protected static $cacheRawB = [
        self::QUERY_WHERE => 1,
        self::ARR_ORDERBY => 1,
        self::ARR_GROUPBY => 1,
        self::ARR_PLUCK_INTO => 1,
    ];

    protected static $resultCache = [];
}
