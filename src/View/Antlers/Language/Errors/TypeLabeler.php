<?php

namespace Statamic\View\Antlers\Language\Errors;

use Statamic\View\Antlers\Language\Nodes\ArgumentGroup;
use Statamic\View\Antlers\Language\Nodes\Constants\FalseConstant;
use Statamic\View\Antlers\Language\Nodes\Constants\NullConstant;
use Statamic\View\Antlers\Language\Nodes\Constants\TrueConstant;
use Statamic\View\Antlers\Language\Nodes\MethodInvocationNode;
use Statamic\View\Antlers\Language\Nodes\ModifierNameNode;
use Statamic\View\Antlers\Language\Nodes\Modifiers\ModifierChainNode;
use Statamic\View\Antlers\Language\Nodes\Modifiers\ModifierNode;
use Statamic\View\Antlers\Language\Nodes\Modifiers\ModifierParameterNode;
use Statamic\View\Antlers\Language\Nodes\ModifierValueNode;
use Statamic\View\Antlers\Language\Nodes\NumberNode;
use Statamic\View\Antlers\Language\Nodes\Operators\Arithmetic\AdditionOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Arithmetic\DivisionOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Arithmetic\ExponentiationOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Arithmetic\FactorialOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Arithmetic\ModulusOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Arithmetic\MultiplicationOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Arithmetic\SubtractionOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Assignment\AdditionAssignmentOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Assignment\DivisionAssignmentOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Assignment\LeftAssignmentOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Assignment\ModulusAssignmentOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Assignment\MultiplicationAssignmentOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Assignment\SubtractionAssignmentOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\EqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\GreaterThanCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\GreaterThanEqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\LessThanCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\LessThanEqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\NotEqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\NotStrictEqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\SpaceshipCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\StrictEqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\ConditionalVariableFallbackOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\LanguageOperatorConstruct;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalAndOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalNegationOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalOrOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalXorOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\NullCoalesceOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\ScopeAssignmentOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\StringConcatenationOperator;
use Statamic\View\Antlers\Language\Nodes\Parameters\ParameterNode;
use Statamic\View\Antlers\Language\Nodes\Paths\PathNode;
use Statamic\View\Antlers\Language\Nodes\RecursiveNode;
use Statamic\View\Antlers\Language\Nodes\StringValueNode;
use Statamic\View\Antlers\Language\Nodes\Structures\ArgSeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\ArrayNode;
use Statamic\View\Antlers\Language\Nodes\Structures\ConditionalFallbackGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\DirectionGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\InlineBranchSeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\InlineTernarySeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicGroupBegin;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicGroupEnd;
use Statamic\View\Antlers\Language\Nodes\Structures\ModifierSeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\ModifierValueSeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\NullCoalescenceGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\ScopedLogicGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\SemanticGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\StatementSeparatorNode;
use Statamic\View\Antlers\Language\Nodes\Structures\SwitchCase;
use Statamic\View\Antlers\Language\Nodes\Structures\SwitchGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\TernaryCondition;
use Statamic\View\Antlers\Language\Nodes\Structures\TupleList;
use Statamic\View\Antlers\Language\Nodes\Structures\TupleListStart;
use Statamic\View\Antlers\Language\Nodes\Structures\ValueDirectionNode;
use Statamic\View\Antlers\Language\Nodes\VariableNode;

class TypeLabeler
{
    const TOKEN_LANG_OPERATOR = 'T_LANG_OPERATOR';
    const TOKEN_VARIABLE = 'T_VAR';
    const TOKEN_NUMBER = 'T_NUMERIC';
    const TOKEN_STRING = 'T_STRING';
    const TOKEN_RECURSIVE = 'T_RECURSIVE';
    const TOKEN_CONSTANT_FALSE = 'T_FALSE';
    const TOKEN_CONSTANT_NULL = 'T_NULL';
    const TOKEN_CONSTANT_TRUE = 'T_TRUE';
    const TOKEN_MODIFIER_NODE = 'T_MODIFIER';
    const TOKEN_MODIFIER_CHAIN = 'T_MODIFIER_CHAIN';
    const TOKEN_MODIFIER_PARAMETER = 'T_MODIFIER_PARAM';
    const TOKEN_OP_A_ADD = 'T_AOP_ADD';
    const TOKEN_OP_A_DIVIDE = 'T_AOP_DIVIDE';
    const TOKEN_OP_A_EXPONENTIATION = 'T_AOP_EXP';
    const TOKEN_OP_A_MODULUS = 'T_AOP_MOD';
    const TOKEN_OP_A_MULTIPLY = 'T_AOP_MULTIPLY';
    const TOKEN_OP_A_SUBTRACT = 'T_AOP_SUBTRACT';
    const TOKEN_OP_A_FACTORIAL = 'T_AOP_FACTORIAL';
    const TOKEN_ASG_ADD = 'T_ASG_ADD';
    const TOKEN_ASG_DIVIDE = 'T_ASG_DIVIDE';
    const TOKEN_ASG_ASSIGN = 'T_ASG';
    const TOKEN_ASG_MODULUS = 'T_ASG_MODULUS';
    const TOKEN_ASG_MULTIPLY = 'T_ASG_MULTIPLY';
    const TOKEN_ASG_SUBTRACT = 'T_ASG_SUBTRACT';
    const TOKEN_CMP_EQUAL = 'T_CMP_EQ';
    const TOKEN_CMP_SEQUAL = 'T_CMP_SEQ';
    const TOKEN_CMP_GT = 'T_CMP_GT';
    const TOKEN_CMP_GTE = 'T_CMP_GTE';
    const TOKEN_CMP_LT = 'T_CMP_LT';
    const TOKEN_CMP_LTE = 'T_CMP_LTE';
    const TOKEN_CMP_NEQ = 'T_CMP_NEQ';
    const TOKEN_CMP_SNEQ = 'T_CMP_SNEQ';
    const TOKEN_CMP_SPACESHIP = 'T_CMP_SPSHP';
    const TOKEN_OP_VARIABLE_FALLBACK = 'T_VFBK';
    const TOKEN_COND_FALLBACK_GROUP = 'T_VFBK_GROUP';
    const TOKEN_BRANCH_SEPARATOR = 'T_BRANCH_SEPARATOR';
    const TOKEN_TERNARY_SEPARATOR = 'T_TERNARY_SEPARATOR';
    const TOKEN_GROUP_BEGIN = 'T_LOGIC_BEGIN';
    const TOKEN_GROUP_END = 'T_LOGIC_END';
    const TOKEN_MODIFIER_SEPARATOR = 'T_MODIFIER_SEPARATOR';
    const TOKEN_MODIFIER_NAME = 'T_MODIFIER_NAME';
    const TOKEN_MODIFIER_VALUE = 'T_MODIFIER_VALUE';
    const TOKEN_MODIFIER_VALUE_SEPARATOR = 'T_MODIFIER_VALUE_SEPARATOR';
    const TOKEN_NULL_COALESCE_GROUP = 'T_NULL_COALESCE_GROUP';
    const TOKEN_SEMANTIC_GROUP = 'T_SEMANTIC_GROUP';
    const TOKEN_STATEMENT_SEPARATOR = 'T_STATEMENT_SEPARATOR';
    const TOKEN_TERNARY_CONDITION = 'T_TERNARY_CONDITION';
    const TOKEN_OP_AND = 'T_AND';
    const TOKEN_OP_LOGIC_NEGATION = 'T_LOGIC_INVERSE';
    const TOKEN_OP_OR = 'T_OR';
    const TOKEN_OP_XOR = 'T_XOR';
    const TOKEN_OP_NULL_COALESCE = 'T_NULL_COALESCE';
    const TOKEN_PARAM = 'T_PARAM';
    const TOKEN_PATH_ACCESSOR = 'T_VAR_SEPARATOR';
    const TOKEN_ARG_GROUP = 'T_ARG_GROUP';
    const TOKEN_ARG_SEPARATOR = 'T_ARG_SEPARATOR';
    const TOKEN_OP_STRING_CONCAT = 'T_STR_CONCAT';
    const TOKEN_OP_SCOPE_REASSIGNMENT = 'T_SCOPE_ASSIGNMENT';

    const TOKEN_STRUCT_SWITCH_CASE = 'T_SWITCH_CASE';
    const TOKEN_STRUCT_SWITCH_GROUP = 'T_SWITCH_GROUP';

    const TOKEN_STRUCT_DIRECTION_GROUP = 'T_DIRECTION_GROUP';
    const TOKEN_STRUCT_VALUE_DIRECTION = 'T_ORDER_DIRECTION';
    const TOKEN_STRUCT_SCOPED_LOGIC_GROUP = 'T_SCOPED_LOGIC_GROUP';
    const TOKEN_STRUCT_LOGIC_GROUP = 'T_LOGIC_GROUP';
    const TOKEN_STRUCT_ARRAY = 'T_ARRAY';
    const TOKEN_STRUCT_T_LIST_START = 'T_LIST_START';
    const TOKEN_STRUCT_TUPLE_LIST = 'T_LIST';
    const TOKEN_STRUCT_METHOD_CALL = 'T_METHOD_CALL';

    public static function getPrettyTypeName($token)
    {
        if ($token instanceof LanguageOperatorConstruct) {
            return self::TOKEN_LANG_OPERATOR;
        } elseif ($token instanceof AdditionOperator) {
            return self::TOKEN_OP_A_ADD;
        } elseif ($token instanceof VariableNode) {
            return self::TOKEN_VARIABLE;
        } elseif ($token instanceof StringValueNode) {
            return self::TOKEN_STRING;
        } elseif ($token instanceof NumberNode) {
            return self::TOKEN_NUMBER;
        } elseif ($token instanceof FalseConstant) {
            return self::TOKEN_CONSTANT_FALSE;
        } elseif ($token instanceof NullConstant) {
            return self::TOKEN_CONSTANT_NULL;
        } elseif ($token instanceof TrueConstant) {
            return self::TOKEN_CONSTANT_TRUE;
        } elseif ($token instanceof ModifierNode) {
            return self::TOKEN_MODIFIER_NODE;
        } elseif ($token instanceof ModifierChainNode) {
            return self::TOKEN_MODIFIER_CHAIN;
        } elseif ($token instanceof ModifierParameterNode) {
            return self::TOKEN_MODIFIER_PARAMETER;
        } elseif ($token instanceof DivisionOperator) {
            return self::TOKEN_OP_A_DIVIDE;
        } elseif ($token instanceof ExponentiationOperator) {
            return self::TOKEN_OP_A_EXPONENTIATION;
        } elseif ($token instanceof ModulusOperator) {
            return self::TOKEN_OP_A_MODULUS;
        } elseif ($token instanceof MultiplicationOperator) {
            return self::TOKEN_OP_A_MULTIPLY;
        } elseif ($token instanceof SubtractionOperator) {
            return self::TOKEN_OP_A_SUBTRACT;
        } elseif ($token instanceof AdditionAssignmentOperator) {
            return self::TOKEN_ASG_ADD;
        } elseif ($token instanceof DivisionAssignmentOperator) {
            return self::TOKEN_ASG_DIVIDE;
        } elseif ($token instanceof ModulusAssignmentOperator) {
            return self::TOKEN_ASG_MODULUS;
        } elseif ($token instanceof MultiplicationAssignmentOperator) {
            return self::TOKEN_ASG_MULTIPLY;
        } elseif ($token instanceof LeftAssignmentOperator) {
            return self::TOKEN_ASG_ASSIGN;
        } elseif ($token instanceof SubtractionAssignmentOperator) {
            return self::TOKEN_ASG_SUBTRACT;
        } elseif ($token instanceof EqualCompOperator) {
            return self::TOKEN_CMP_EQUAL;
        } elseif ($token instanceof StrictEqualCompOperator) {
            return self::TOKEN_CMP_SEQUAL;
        } elseif ($token instanceof GreaterThanCompOperator) {
            return self::TOKEN_CMP_GT;
        } elseif ($token instanceof GreaterThanEqualCompOperator) {
            return self::TOKEN_CMP_GTE;
        } elseif ($token instanceof LessThanCompOperator) {
            return self::TOKEN_CMP_LT;
        } elseif ($token instanceof LessThanEqualCompOperator) {
            return self::TOKEN_CMP_LTE;
        } elseif ($token instanceof NotEqualCompOperator) {
            return self::TOKEN_CMP_NEQ;
        } elseif ($token instanceof NotStrictEqualCompOperator) {
            return self::TOKEN_CMP_SNEQ;
        } elseif ($token instanceof SpaceshipCompOperator) {
            return self::TOKEN_CMP_SPACESHIP;
        } elseif ($token instanceof ConditionalVariableFallbackOperator) {
            return self::TOKEN_OP_VARIABLE_FALLBACK;
        } elseif ($token instanceof ConditionalFallbackGroup) {
            return self::TOKEN_COND_FALLBACK_GROUP;
        } elseif ($token instanceof InlineBranchSeparator) {
            return self::TOKEN_BRANCH_SEPARATOR;
        } elseif ($token instanceof InlineTernarySeparator) {
            return self::TOKEN_TERNARY_SEPARATOR;
        } elseif ($token instanceof LogicGroupBegin) {
            return self::TOKEN_GROUP_BEGIN;
        } elseif ($token instanceof LogicGroupEnd) {
            return self::TOKEN_GROUP_END;
        } elseif ($token instanceof ModifierSeparator) {
            return self::TOKEN_MODIFIER_SEPARATOR;
        } elseif ($token instanceof ModifierNameNode) {
            return self::TOKEN_MODIFIER_NAME;
        } elseif ($token instanceof ModifierValueNode) {
            return self::TOKEN_MODIFIER_VALUE;
        } elseif ($token instanceof ModifierValueSeparator) {
            return self::TOKEN_MODIFIER_VALUE_SEPARATOR;
        } elseif ($token instanceof NullCoalescenceGroup) {
            return self::TOKEN_NULL_COALESCE_GROUP;
        } elseif ($token instanceof SemanticGroup) {
            return self::TOKEN_SEMANTIC_GROUP;
        } elseif ($token instanceof StatementSeparatorNode) {
            return self::TOKEN_STATEMENT_SEPARATOR;
        } elseif ($token instanceof TernaryCondition) {
            return self::TOKEN_TERNARY_CONDITION;
        } elseif ($token instanceof LogicalAndOperator) {
            return self::TOKEN_OP_AND;
        } elseif ($token instanceof LogicalNegationOperator) {
            return self::TOKEN_OP_LOGIC_NEGATION;
        } elseif ($token instanceof LogicalOrOperator) {
            return self::TOKEN_OP_OR;
        } elseif ($token instanceof LogicalXorOperator) {
            return self::TOKEN_OP_XOR;
        } elseif ($token instanceof NullCoalesceOperator) {
            return self::TOKEN_OP_NULL_COALESCE;
        } elseif ($token instanceof ParameterNode) {
            return self::TOKEN_PARAM;
        } elseif ($token instanceof PathNode) {
            return self::TOKEN_PATH_ACCESSOR;
        } elseif ($token instanceof FactorialOperator) {
            return self::TOKEN_OP_A_FACTORIAL;
        } elseif ($token instanceof RecursiveNode) {
            return self::TOKEN_RECURSIVE;
        } elseif ($token instanceof ArgumentGroup) {
            return self::TOKEN_ARG_GROUP;
        } elseif ($token instanceof ArgSeparator) {
            return self::TOKEN_ARG_SEPARATOR;
        } elseif ($token instanceof StringConcatenationOperator) {
            return self::TOKEN_OP_STRING_CONCAT;
        } elseif ($token instanceof ScopeAssignmentOperator) {
            return self::TOKEN_OP_SCOPE_REASSIGNMENT;
        } elseif ($token instanceof SwitchCase) {
            return self::TOKEN_STRUCT_SWITCH_CASE;
        } elseif ($token instanceof SwitchGroup) {
            return self::TOKEN_STRUCT_SWITCH_GROUP;
        } elseif ($token instanceof DirectionGroup) {
            return self::TOKEN_STRUCT_DIRECTION_GROUP;
        } elseif ($token instanceof ScopedLogicGroup) {
            return self::TOKEN_STRUCT_SCOPED_LOGIC_GROUP;
        } elseif ($token instanceof LogicGroup) {
            return self::TOKEN_STRUCT_LOGIC_GROUP;
        } elseif ($token instanceof ValueDirectionNode) {
            return self::TOKEN_STRUCT_VALUE_DIRECTION;
        } elseif ($token instanceof ArrayNode) {
            return self::TOKEN_STRUCT_ARRAY;
        } elseif ($token instanceof TupleListStart) {
            return self::TOKEN_STRUCT_T_LIST_START;
        } elseif ($token instanceof TupleList) {
            return self::TOKEN_STRUCT_TUPLE_LIST;
        } elseif ($token instanceof MethodInvocationNode) {
            return self::TOKEN_STRUCT_METHOD_CALL;
        }

        return get_class($token);
    }

    public static function getPrettyRuntimeTypeName($value)
    {
        if (is_string($value)) {
            return 'string';
        } elseif (is_numeric($value)) {
            return 'numeric';
        } elseif ($value === null) {
            return 'null';
        } elseif ($value == false) {
            return 'bool';
        } elseif ($value == true) {
            return 'bool';
        }

        return gettype($value);
    }
}
