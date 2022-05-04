<?php

namespace Statamic\Fieldtypes\Bard;

use HtmlToProseMirror\Marks\Link as DefaultLinkMarkHtml;
use HtmlToProseMirror\Renderer;
use Statamic\Fieldtypes\Bard\LinkMarkHtml as CustomLinkMarkHtml;

class Deaugmentor
{
    protected $fieldtype;

    protected static $customMarks = [];
    protected static $customNodes = [];
    protected static $replaceMarks = [];
    protected static $replaceNodes = [];

    public function __construct($fieldtype)
    {
        $this->fieldtype = $fieldtype;
    }

    public function deaugment($value)
    {
        $value = $this->removeStatamicUrlPrefix($value);
        $value = $this->convertToProseMirror($value);

        return $value;
    }

    protected function removeStatamicUrlPrefix($value)
    {
        return str_replace('statamic://', '', $value);
    }

    public function convertToProseMirror($value)
    {
        $renderer = (new Renderer)
            ->replaceMark(DefaultLinkMarkHtml::class, CustomLinkMarkHtml::class)
            ->addNodes(static::$customNodes)
            ->addMarks(static::$customMarks);

        foreach (static::$replaceNodes as $searchNode => $replaceNode) {
            $renderer->replaceNode($searchNode, $replaceNode);
        }

        foreach (static::$replaceMarks as $searchMark => $replaceMark) {
            $renderer->replaceMark($searchMark, $replaceMark);
        }

        return $renderer->render($value)['content'];
    }

    public static function addNode($node)
    {
        static::$customNodes[$node] = $node;
    }

    public static function addMark($mark)
    {
        static::$customMarks[$mark] = $mark;
    }

    public static function replaceNode($searchNode, $replaceNode)
    {
        static::$replaceNodes[$searchNode] = $replaceNode;
    }

    public static function replaceMark($searchMark, $replaceMark)
    {
        static::$replaceMarks[$searchMark] = $replaceMark;
    }
}
