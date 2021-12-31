<?php

namespace Statamic\View\Antlers\Language\Errors;

/**
 * Range 500-699 error codes are reserved for linter error codes.
 * Please do not use them for parser errors or runtime errors.
 */
class AntlersErrorCodes
{
    const TYPE_EXPECTING_OPERAND = 'ANTLR_001';
    const TYPE_UNEXPECTED_END_OF_INPUT = 'ANTLR_002';
    const TYPE_RUNTIME_TYPE_MISMATCH = 'ANTLR_003';
    const TYPE_RUNTIME_DIVIDE_BY_ZERO = 'ANTLR_004';
    const TYPE_RUNTIME_UNKNOWN_LANG_OPERATOR = 'ANTLR_005';
    const TYPE_RUNTIME_UNEXPECTED_STACK_CONDITION = 'ANTLR_006';
    const TYPE_RUNTIME_PARSE_VALUE_VIOLATION = 'ANTLR_007';
    const TYPE_PARSE_UNCLOSED_CONDITIONAL = 'ANTLR_008';
    const TYPE_PARSE_EMPTY_CONDITIONAL = 'ANTLR_009';
    const TYPE_PARSE_UNPAIRED_CONDITIONAL = 'ANTLR_008';
    const TYPE_ILLEGAL_STRING_ESCAPE_SEQUENCE = 'ANTLR_009';
    const TYPE_INCOMPLETE_ANTLERS_REGION = 'ANTLR_010';
    const TYPE_INCOMPLETE_ANTELRS_COMMENT_REGION = 'ANTLR_011';
    const TYPE_ILLEGAL_VARPATH_RIGHT = 'ANTLR_012';
    const TYPE_ILLEGAL_DOTVARPATH_RIGHT = 'ANTLR_013';
    const TYPE_ILLEGAL_VARPATH_SUBPATH_START = 'ANTLR_014';
    const TYPE_ILLEGAL_VARPATH_SPACE_RIGHT = 'ANTLR_015';
    const TYPE_UNEXPECTED_EOI_VARPATH_ACCESSOR = 'ANTLR_016';
    const TYPE_ILLEGAL_VARIABLE_NAME = 'ANTLR_017';
    const TYPE_UNSET_MODIFIER_DETAILS = 'ANTLR_018';
    const TYPE_MODIFIER_NAME_NOT_START_OF_DETAILS = 'ANTLR_019';
    const TYPE_MODIFIER_UNEXPECTED_VALUE = 'ANTLR_020';
    const TYPE_MODIFIER_UNEXPECTED_END_OF_VALUE_LIST = 'ANTLR_021';
    const TYPE_TERNARY_EXPECTING_BRANCH_SEPARATOR = 'ANTLR_022';
    const TYPE_TERNARY_UNEXPECTED_EXPRESSION_LENGTH = 'ANTLR_023';
    const TYPE_RECURSIVE_UNPAIRED_NODE = 'ANTLR_024';
    const TYPE_RECURSIVE_NODE_INVALID_POSITION = 'ANTLR_025';
    const TYPE_NO_PARSE_UNASSOCIATED = 'ANTLR_026';
    const TYPE_RECURSIVE_NODE_UNASSOCIATED_PARENT = 'ANTLR_027';
    const TYPE_LOGIC_GROUP_NO_END = 'ANTLR_028';
    const TYPE_LOGIC_GROUP_NO_START = 'ANTLR_029';
    const TYPE_UNEXPECTED_BRANCH_SEPARATOR = 'ANTLR_030';
    const TYPE_UNEXPECTED_BRANCH_SEPARATOR_FOR_VARCOMBINE = 'ANTLR_031';
    const TYPE_UNEXPECTED_MODIFIER_SEPARATOR = 'ANTLR_032';
    const TYPE_UNEXPECTED_EOI_WHILE_PARSING_MODIFIER_DETAILS = 'ANTLR_033';
    const TYPE_UNEXPECTED_EOI_WHILE_PARSING_MODIFIER_VALUE = 'ANTLR_034';
    const TYPE_UNEXPECTED_EOI_WHILE_PARSING_NODE_PARAMETER = 'ANTLR_035';
    const TYPE_UNEXPECTED_EOI_WHILE_MANIFESTING_ANTLERS_NODE = 'ANTLR_036';
    const TYPE_UNEXPECTED_EOI_WHILE_REDUCING_NEGATION_OPERATORS = 'ANTLR_037';
    const TYPE_UNEXPECTED_EOI_WHILE_PARSING_SEMANTIC_GROUP = 'ANTLR_038';
    const TYPE_UNEXPECTED_EOI_WHILE_PARSING_NULL_COALESCENCE_GROUP = 'ANTLR_039';
    const TYPE_UNEXPECTED_EOI_WHILE_PARSING_TERNARY_GROUP = 'ANTLR_040';
    const TYPE_UNEXPECTED_EOI_WHILE_PARSING_TERNARY_GROUP_FALSE_BRANCH = 'ANTLR_041';
    const TYPE_UNEXPECTED_EOI_WHILE_EXITING_TERNARY_GROUP = 'ANTLR_042';
    const TYPE_UNEXPECTED_EOI_WHILE_PARSING_LOGIC_GROUP_NEGATION_OFFSET = 'ANTLR_043';
    const TYPE_UNEXPECTED_EOI_WHILE_PARSING_LOGIC_GROUP_END_DUE_TO_NEGATION = 'ANTLR_044';
    const TYPE_UNEXPECTED_EOI_WHILE_PARSING_LOGIC_GROUP_END = 'ANTLR_045';
    const TYPE_RUNTIME_UNKNOWN_LIBRARY = 'ANTLR_046';
    const TYPE_RUNTIME_LIBRARY_BAD_METHOD_CALL = 'ANTLR_047';
    const TYPE_UNEXPECTED_FACTORIAL_WHILE_CREATING_GROUPS = 'ANTLR_048';
    const TYPE_UNEXPECTED_FACTORIAL_OPERAND = 'ANTLR_049';
    const TYPE_UNEXPECTED_LOGIC_NEGATION_OPERATOR = 'ANTLR_050';
    const TYPE_FACTORIAL_MATERIALIZED_BOOL_DETECTED = 'ANTLR_051';
    const TYPE_ARG_UNEXPECTED_NAMED_ARGUMENT = 'ANTLR_052';
    const TYPE_UNEXPECTED_EOI_PARSING_BRANCH_GROUP = 'ANTLR_053';
    const TYPE_UNEXPECTED_UNNAMED_METHOD_ARGUMENT = 'ANTLR_054';
    const TYPE_LIBRARY_CALL_NO_ARGS_PROVIDED = 'ANTLR_055';
    const TYPE_LIBRARY_CALL_MISSING_REQUIRED_FORMAL_ARG = 'ANTLR_056';
    const TYPE_LIBRARY_CALL_RUNTIME_TYPE_MISMATCH = 'ANTLR_057';
    const TYPE_LIBRARY_CALL_UNEXPECTED_ARG_RESOLVE_FAULT = 'ANTLR_058';
    const TYPE_LIBRARY_CALL_TOO_MANY_ARGUMENTS = 'ANTLR_059';
    const TYPE_UNEXPECTED_TOKEN_WHILE_PARSING_METHOD = 'ANTLR_060';
    const TYPE_INVALID_NAMED_ARG_IDENTIFIER = 'ANTLR_061';
    const TYPE_LIBRARY_CALL_INVALID_ARGUMENT_NAME = 'ANTLR_062';
    const TYPE_RUNTIME_ATTEMPT_TO_OVERWRITE_LOADED_LIBRARY = 'ANTLR_063';
    const TYPE_UNEXPECTED_EOI_WHILE_PARSING_ARG_GROUP = 'ANTLR_064';
    const TYPE_EXPECTING_ARGUMENT_GROUP = 'ANTLR_065';
    const TYPE_RUNTIME_PROTECTED_LIBRARY_ACCESS_VIOLATION = 'ANTLR_066';
    const TYPE_RUNTIME_FATAL_UNPAIRED_LOOP_END = 'ANTLR_067';
    const TYPE_UNEXPECTED_OPERATOR = 'ANTLR_068';
    const TYPE_OPERATOR_INVALID_ON_RIGHT = 'ANTLR_069';
    const TYPE_INVALID_ASSIGNMENT_LOOP_PAIR = 'ANTLR_070';
    const TYPE_UNEXPECTED_EOI_PARSING_ORDER_GROUP = 'ANTLR_071';
    const TYPE_EXPECTING_ORDER_GROUP_FOR_ORDER_BY_OPERAND = 'ANTLR_072';
    const TYPE_QUERY_UNSUPPORTED_VALUE_TYPE = 'ANTLR_073';
    const TYPE_UNEXPECTED_RUNTIME_RESULT_FOR_ORDER_BY_CLAUSE = 'ANTLR_074';
    const TYPE_UNEXPECTED_EMPTY_DIRECTION_GROUP = 'ANTLR_075';
    const TYPE_INVALID_ORDER_BY_NAME_VALUE = 'ANTLR_076';
    const TYPE_INVALID_ORDER_BY_SORT_VALUE = 'ANTLR_077';
    const TYPE_UNEXPECTED_TOKEN_FOR_GROUP_BY = 'ANTLR_78';
    const TYPE_UNEXPECTED_EOI_WHILE_PARSING_GROUP_BY = 'ANTLR_079';
    const TYPE_UNEXPECTED_GROUP_BY_AS_ALIAS_TYPE = 'ANTLR_080';
    const TYPE_UNEXPECTED_EOI_WHILE_PARSING_SWITCH_GROUP = 'ANTLR_081';
    const TYPE_UNEXPECTED_TOKEN_FOR_SWITCH_GROUP = 'ANTLR_082';
    const TYPE_UNEXPECTED_SWITCH_START_VALUE = 'ANTLR_083';
    const TYPE_UNEXPECTED_SWITCH_START_VALUE_NO_VALUE = 'ANTLR_084';
    const TYPE_UNEXPECTED_SWITCH_START_VALUE_NO_SEMANTIC_VALUE = 'ANTLR_085';
    const TYPE_SWITCH_DEFAULT_MUST_BE_LAST = 'ANTLR_086';
    const TYPE_PARSER_INVALID_SWITCH_TOKEN = 'ANTLR_087';
    const TYPE_ORDER_BY_INVALID_RETURN_TYPE = 'ANTLR_088';
    const TYPE_GROUP_BY_SCOPED_GROUP_MUST_BE_ENCLOSED = 'ANTLR_089';
    const TYPE_PLUCK_INTO_MISSING_VARIABLE_TARGET = 'ANTLR_090';
    const TYPE_PLUCK_INTO_INVALID_VARIABLE_TARGET = 'ANTLR_091';
    const TYPE_PLUCK_INTO_NO_PREDICATE = 'ANTLR_092';
    const TYPE_PLUCK_INTO_INVALID_PREDICATE_VALUE = 'ANTLR_093';
    const TYPE_PLUCK_INTO_EMPTY_LOGIC_GROUP = 'ANTLR_094';
    const TYPE_PLUCK_INTO_INVALID_NUMBER_OF_TUPLE_VARIABLES = 'ANTLR_095';
    const TYPE_PLUCK_INTO_UNKNOWN_ALIAS_VARNAME = 'ANTLR_096';
    const TYPE_PLUCK_INTO_UNEXPECTED_EMPTY_T_LOGIC_GROUP = 'ANTLR_097';
    const TYPE_PLUCK_INTO_REFERENCE_TYPE_DYNAMIC = 'ANTLR_098';
    const TYPE_PLUCK_INTO_REFERENCE_AMBIGUOUS = 'ANTLR_099';
    const TYPE_RUNTIME_ASSIGNMENT_TO_NON_VAR = 'ANTLR_100';
    const TYPE_ARR_MAKE_MISSING_TARGET = 'ANTLR_101';
    const TYPE_ARR_MAKE_UNEXPECTED_TYPE = 'ANTLR_102';
    const TYPE_ARR_MAKE_MISSING_ARR_KEY_PAIR_VALUE = 'ANTLR_103';
    const TYPE_ARR_KEY_PAIR_INVALID_KEY_TYPE = 'ANTLR_104';
    const TYPE_ARR_UNEXPECT_ARG_SEPARATOR = 'ANTLR_105';
    const TYPE_ARR_KEY_PAIR_MISSING_KEY = 'ANTLR_106';
    const RUNTIME_PROTECTED_VAR_ACCESS = 'ANTLR_107';
    const RUNTIME_PROTECTED_TAG_ACCESS = 'ANTLR_108';
    const RUNTIME_PROTECTED_MODIFIER_ACCESS = 'ANTLR_109';
    const TYPE_INCOMPLETE_PHP_EVALUATION_REGION = 'ANTLR_110';
    const RUNTIME_PHP_NODE_WHEN_PHP_DISABLED = 'ANTLR_111';
    const RUNTIME_PHP_NODE_USER_CONTENT_TAG = 'ANTLR_112';
    const TYPE_UNEXPECTED_TYPE_FOR_TUPLE_LIST = 'ANTLR_113';
    const TYPE_UNEXPECTED_EOI_PARSING_TUPLE_LIST = 'ANTLR_114';

    const TYPE_MISSING_BODY_TUPLE_LIST = 'ANTLR_115';
    const TYPE_MISSING_NAMES_TUPLE_LIST = 'ANTLR_116';
    const TYPE_VALUE_NAME_LENGTH_MISMATCH_TUPLE_LIST = 'ANTLR_117';
    const TYPE_INVALID_TUPLE_LIST_NAME_TYPE = 'ANTLR_118';
    const TYPE_INVALID_MANIFESTED_NAME_GROUP = 'ANTLR_119';
    const TYPE_INVALID_TUPLE_LIST_VALUE_TYPE_GROUP = 'ANTLR_120';
    const TYPE_INVALID_TUPLE_LIST_VALUE_TYPE = 'ANTLR_121';
    const TYPE_RUNTIME_BAD_METHOD_CALL = 'ANTLR_122';

    const TYPE_METHOD_CALL_MISSING_ARG_GROUP = 'ANTLR_123';
    const TYPE_INVALID_METHOD_CALL_ARG_GROUP = 'ANTLR_124';
    const TYPE_METHOD_CALL_MISSING_METHOD = 'ANTLR_125';
    const TYPE_MODIFIER_NOT_FOUND = 'ANTLR_126';
    const TYPE_RUNTIME_GENERAL_FAULT = 'ANTLR_127';
    const TYPE_MODIFIER_INCORRECT_VALUE_POSITION = 'ANTLR_128';
}
