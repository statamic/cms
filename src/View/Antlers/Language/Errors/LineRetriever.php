<?php

namespace Statamic\View\Antlers\Language\Errors;

use PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use PhpParser\Node\Expr\BinaryOp\LogicalOr;
use PhpParser\Node\Expr\BinaryOp\LogicalXor;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\Constants\FalseConstant;
use Statamic\View\Antlers\Language\Nodes\Constants\NullConstant;
use Statamic\View\Antlers\Language\Nodes\Constants\TrueConstant;
use Statamic\View\Antlers\Language\Nodes\MethodInvocationNode;
use Statamic\View\Antlers\Language\Nodes\ModifierNameNode;
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
use Statamic\View\Antlers\Language\Nodes\Operators\NullCoalesceOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\ScopeAssignmentOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\StringConcatenationOperator;
use Statamic\View\Antlers\Language\Nodes\StringValueNode;
use Statamic\View\Antlers\Language\Nodes\Structures\ArgSeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\InlineBranchSeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\InlineTernarySeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicGroupBegin;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicGroupEnd;
use Statamic\View\Antlers\Language\Nodes\Structures\ModifierSeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\ModifierValueSeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\StatementSeparatorNode;
use Statamic\View\Antlers\Language\Nodes\Structures\TupleListStart;
use Statamic\View\Antlers\Language\Nodes\VariableNode;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;

class LineRetriever
{
    public static function getErrorLine(AbstractNode $node)
    {
        $line = 1;

        if ($node instanceof AntlersNode) {
            if ($node->originalNode != null) {
                $line = $node->originalNode->startPosition->line;
            } elseif ($node->startPosition != null) {
                $line = $node->startPosition->line;
            }
        } else {
            if ($node->originalAbstractNode != null) {
                $line = $node->originalAbstractNode->startPosition->line;
            } elseif ($node->startPosition != null) {
                $line = $node->startPosition->line;
            }
        }

        return $line;
    }

    public static function getErrorLineAndCharText(AbstractNode $node)
    {
        $line = 1;
        $char = 1;

        if ($node instanceof AntlersNode) {
            if ($node->originalNode != null) {
                $line = $node->originalNode->startPosition->line;
                $char = $node->originalNode->startPosition->char;
            } elseif ($node->startPosition != null) {
                $line = $node->startPosition->line;
                $char = $node->startPosition->char;
            }
        } else {
            if ($node->originalAbstractNode != null) {
                $line = $node->originalAbstractNode->startPosition->line;
                $char = $node->originalAbstractNode->startPosition->char;
            } elseif ($node->startPosition != null) {
                $line = $node->startPosition->line;
                $char = $node->startPosition->char;
            }
        }

        return 'Line '.$line.' char '.$char.'.';
    }

    public static function getNearText(AbstractNode $node)
    {
        if ($node instanceof VariableNode) {
            return $node->name;
        } elseif ($node instanceof NumberNode) {
            return $node->value;
        } elseif ($node instanceof StringValueNode) {
            return $node->value;
        } elseif ($node instanceof ModifierNameNode) {
            return $node->name;
        } elseif ($node instanceof ModifierValueNode) {
            return $node->value;
        } elseif ($node instanceof TupleListStart) {
            return $node->content;
        }

        // Simply dump the original content for these lexer/parser types.
        if ($node instanceof LogicalAnd || $node instanceof LogicalOr || $node instanceof LogicalXor ||
            $node instanceof NullConstant || $node instanceof TrueConstant || $node instanceof FalseConstant ||
            $node instanceof LogicalNegationOperator || $node instanceof LanguageOperatorConstruct ||
            $node instanceof ArgSeparator || $node instanceof StatementSeparatorNode || $node instanceof AdditionAssignmentOperator ||
            $node instanceof AdditionOperator || $node instanceof SubtractionAssignmentOperator || $node instanceof  SubtractionOperator ||
            $node instanceof ExponentiationOperator || $node instanceof MultiplicationAssignmentOperator || $node  instanceof MultiplicationOperator ||
            $node instanceof DivisionAssignmentOperator || $node instanceof DivisionOperator || $node instanceof ModulusAssignmentOperator ||
            $node instanceof ModulusOperator || $node instanceof SpaceshipCompOperator || $node instanceof LessThanEqualCompOperator ||
            $node instanceof LessThanCompOperator || $node instanceof GreaterThanEqualCompOperator || $node instanceof GreaterThanCompOperator ||
            $node instanceof LeftAssignmentOperator || $node instanceof StrictEqualCompOperator || $node instanceof EqualCompOperator ||
            $node instanceof LogicalAndOperator || $node instanceof ModifierSeparator || $node instanceof LogicalOrOperator ||
            $node instanceof NotStrictEqualCompOperator || $node instanceof NotEqualCompOperator || $node instanceof ConditionalVariableFallbackOperator ||
            $node instanceof NullCoalesceOperator || $node instanceof InlineTernarySeparator || $node instanceof LogicGroupBegin ||
            $node instanceof LogicGroupEnd || $node instanceof ModifierValueSeparator || $node instanceof InlineBranchSeparator ||
            $node instanceof FactorialOperator || $node instanceof ScopeAssignmentOperator || $node instanceof StringConcatenationOperator ||
            $node instanceof MethodInvocationNode) {
            return $node->content;
        }

        $text = '';

        if ($node instanceof AntlersNode) {
            if ($node->originalNode != null) {
                $text = $node->originalNode->getContent();
            } else {
                $text = $node->startPosition->line;
            }
        } else {
            if ($node->originalAbstractNode != null) {
                $text = $node->originalAbstractNode->rawContent();
            } else {
                $text = $node->rawContent();
            }
        }

        if (mb_strlen($text) > 15) {
            $text = StringUtilities::substr($text, -15);
        }

        return $text;
    }
}
