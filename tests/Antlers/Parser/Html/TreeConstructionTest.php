<?php

namespace Tests\Antlers\Parser\Html;

use PHPUnit\Framework\Attributes\DataProvider;
use Statamic\View\Antlers\Language\Analyzers\Html\AntlersNode;
use Statamic\View\Antlers\Language\Analyzers\Html\Element;
use Statamic\View\Antlers\Language\Analyzers\Html\OpaqueNode;
use Statamic\View\Antlers\Language\Analyzers\Html\Text;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Tests\Antlers\ParserTestCase;

class TreeConstructionTest extends ParserTestCase
{
    #[DataProvider('markupProvider')]
    public function test_the_graph_matches_html5_tree_construction($template, $expected)
    {
        $parser = new DocumentParser;
        $parser->parse($template);
        $document = $parser->html();

        $this->assertSame(implode("\n", $expected), rtrim($this->dump($document)));
        $this->assertSame($template, $document->markDirty()->toHtml());
    }

    public static function markupProvider()
    {
        return [
            'adoption agency around a block' => ['<p><b>1<div>2</b>3</div>', [
                '<p>',
                '  <b>',
                '    "1"',
                '<div>',
                '  <b synthetic>',
                '    "2"',
                '  "3"',
            ]],
            'misnested formatting' => ['<b><i>1</b>2</i>', [
                '<b>',
                '  <i>',
                '    "1"',
                '<i synthetic>',
                '  "2"',
            ]],
            'nested anchors' => ['<a>1<a>2</a>3</a>', [
                '<a>',
                '  "1"',
                '<a>',
                '  "2"',
                '"3"',
                '#markup "</a>"',
            ]],
            'formatting across paragraphs' => ['<em><p>x<b>y</em>z</b></p>', [
                '<em>',
                '<p>',
                '  <em synthetic>',
                '    "x"',
                '    <b>',
                '      "y"',
                '  <b synthetic>',
                '    "z"',
            ]],
            'implied paragraph end' => ['<p>1<p>2', [
                '<p>',
                '  "1"',
                '<p>',
                '  "2"',
            ]],
            'implied list item end' => ['<ul><li>a<li>b</ul>', [
                '<ul>',
                '  <li>',
                '    "a"',
                '  <li>',
                '    "b"',
            ]],
            'implied definition list end' => ['<dl><dt>a<dd>b</dl>', [
                '<dl>',
                '  <dt>',
                '    "a"',
                '  <dd>',
                '    "b"',
            ]],
            'implied option end' => ['<select><option>a<option>b</select>', [
                '<select>',
                '  <option>',
                '    "a"',
                '  <option>',
                '    "b"',
            ]],
            'implied table sections' => ['<table><td>x</td></table>', [
                '<table>',
                '  <tbody synthetic>',
                '    <tr synthetic>',
                '      <td>',
                '        "x"',
            ]],
            'foster parented text' => ['<table>foo<tr><td>x</td></tr></table>', [
                '"foo"',
                '<table>',
                '  <tbody synthetic>',
                '    <tr>',
                '      <td>',
                '        "x"',
            ]],
            'foster parented element' => ['<table><div>d</div><tr><td>x</td></tr></table>', [
                '<div>',
                '  "d"',
                '<table>',
                '  <tbody synthetic>',
                '    <tr>',
                '      <td>',
                '        "x"',
            ]],
            'implied cell end' => ['<table><tr><td>a<td>b</table>', [
                '<table>',
                '  <tbody synthetic>',
                '    <tr>',
                '      <td>',
                '        "a"',
                '      <td>',
                '        "b"',
            ]],
            'text in colgroup' => ['<table><colgroup><col>text</table>', [
                '"text"',
                '<table>',
                '  <colgroup>',
                '    <col>',
            ]],
            'nested tables' => ['<table><tr><td><table><tr><td>y</td></tr></table></td></tr></table>', [
                '<table>',
                '  <tbody synthetic>',
                '    <tr>',
                '      <td>',
                '        <table>',
                '          <tbody synthetic>',
                '            <tr>',
                '              <td>',
                '                "y"',
            ]],
            'template contents' => ['<template><tr><td>x</td></tr></template>', [
                '<template>',
                '  <tr>',
                '    <td>',
                '      "x"',
            ]],
            'table end tags see through foreign integration points' => ['<table><tr><td><svg><desc>x</td>{{ a }}<td>y</td></tr></table>', [
                '{{ a }}',
                '<table>',
                '  <tbody synthetic>',
                '    <tr>',
                '      <td>',
                '        <svg>',
                '          <desc>',
                '            "x"',
                '      <td>',
                '        "y"',
            ]],
            'table end inside fostered foreign content' => ['<table><svg><foreignObject></table><p>{{ a }}</p>', [
                '<svg>',
                '  <foreignObject>',
                '<table>',
                '<p>',
                '  {{ a }}',
            ]],
            'foreign elements named like scope boundaries' => ['<b><svg><object></b>{{ a }}', [
                '<b>',
                '  <svg>',
                '    <object>',
                '{{ a }}',
            ]],
            'button scope' => ['<button><p>a<button>b</button>', [
                '<button>',
                '  <p>',
                '    "a"',
                '<button>',
                '  "b"',
            ]],
            'script raw text' => ['<script>if (a < b) {}</script>', [
                '<script>',
                '  "if (a < b) {}"',
            ]],
            'style raw text' => ['<style>a > b {}</style>', [
                '<style>',
                '  "a > b {}"',
            ]],
            'textarea raw text' => ['<textarea><p>x</textarea>', [
                '<textarea>',
                '  "<p>x"',
            ]],
            'title raw text' => ['<title><b>x</title>', [
                '<title>',
                '  "<b>x"',
            ]],
            'truncated script end tag' => ['<script>x</script', [
                '<script>',
                '  "x</script"',
            ]],
            'truncated script with antlers' => ['<script>{{ x }}</script', [
                '<script>',
                '  {{ x }}',
                '  "</script"',
            ]],
            'svg children' => ['<svg><path/><circle></svg>', [
                '<svg>',
                '  <path>',
                '  <circle>',
            ]],
            'svg name case' => ['<svg><lineargradient></lineargradient></svg>', [
                '<svg>',
                '  <linearGradient>',
            ]],
            'foreign object integration point' => ['<svg><foreignObject><div>x</div></foreignObject></svg>', [
                '<svg>',
                '  <foreignObject>',
                '    <div>',
                '      "x"',
            ]],
            'mathml' => ['<math><mi>x</mi></math>', [
                '<math>',
                '  <mi>',
                '    "x"',
            ]],
            'self closing non void' => ['<div/>x', [
                '<div>',
                '  "x"',
            ]],
            'void with slash' => ['<br/>x', [
                '<br>',
                '"x"',
            ]],
            'recovered break' => ['<p>a</br>b</p>', [
                '<p>',
                '  "a"',
                '  <br>',
                '  "b"',
            ]],
            'recovered break inside reconstructed formatting' => ['<table><nobr><table></br></nobr>', [
                '<nobr>',
                '<table>',
                '<nobr synthetic>',
                '  <br>',
                '<table>',
            ]],
            'recovered break fostered out of a table' => ['<p><b><table></br><td>x', [
                '<p>',
                '  <b>',
                '<b synthetic>',
                '  <br>',
                '<table>',
                '  <tbody synthetic>',
                '    <tr synthetic>',
                '      <td>',
                '        "x"',
            ]],
            'bogus closing tag' => ['</bogus>x', [
                '#markup "</bogus>"',
                '"x"',
            ]],
            'comment' => ['<!-- c -->x', [
                '#comment "<!-- c -->"',
                '"x"',
            ]],
            'carriage returns' => ["<p>a\r{{ x }}\r\n<b>b</b>\n</p>", [
                '<p>',
                '  "a\n"',
                '  {{ x }}',
                '  "\n"',
                '  <b>',
                '    "b"',
                '  "\n"',
            ]],
            'antlers loop in list' => ['<ul>{{ items }}<li>{{ name }}</li>{{ /items }}</ul>', [
                '<ul>',
                '  {{ items }}',
                '  <li>',
                '    {{ name }}',
                '  {{ /items }}',
            ]],
            'antlers in opening tag' => ['<div {{ attrs }}>x</div>', [
                '<div>',
                '  "x"',
            ]],
            'dynamic self closing tag' => ['<x-{{ kind }} /><p>{{ after }}</p>', [
                '<{dynamic}>',
                '<p>',
                '  {{ after }}',
            ]],
            'dynamic paired tag' => ['<x-{{ kind }}>{{ inside }}</x-{{ kind }}><p>{{ after }}</p>', [
                '<{dynamic}>',
                '  {{ inside }}',
                '<p>',
                '  {{ after }}',
            ]],
            'fostered antlers' => ['<table>{{ rows }}<tr><td>x</td></tr>{{ /rows }}</table>', [
                '{{ rows }}',
                '{{ /rows }}',
                '<table>',
                '  <tbody synthetic>',
                '    <tr>',
                '      <td>',
                '        "x"',
            ]],
            'antlers inside comment' => ['<!-- {{ c }} --><p>{{ p }}</p>', [
                '#comment "<!-- {{ c }} -->"',
                '<p>',
                '  {{ p }}',
            ]],
            'antlers inside php block' => ['<p><?php if ($a > 1) { $b = "{{ x }}"; } ?></p><span>{{ y }}</span>', [
                '<p>',
                '  #php "<?php if ($a > 1) { $b = \"{{ x }}\"; } ?>"',
                '<span>',
                '  {{ y }}',
            ]],
            'php block inside a tag' => ['<div <?php if ($a->b): ?>data-x="{{ x }}"<?php endif; ?>><p>{{ y }}</p></div>', [
                '<div>',
                '  <p>',
                '    {{ y }}',
            ]],
            'php block quoting inside a quoted attribute' => ['<div class="<?php echo $a ? "on" : "off" ?>" data-id="{{ id }}"><p>{{ y }}</p></div>', [
                '<div>',
                '  <p>',
                '    {{ y }}',
            ]],
            'php block swallowing a region inside a tag' => ['<div <?php {{ a }} ?> data-x={{ a > b }}>x</div>{{ c }}', [
                '<div>',
                '  "x"',
                '{{ c }}',
            ]],
            'short echo block swallowing a region inside a tag' => ['<div <?= {{ a }} ?> data-x={{ a > b }}>x</div>{{ c }}', [
                '<div>',
                '  "x"',
                '{{ c }}',
            ]],
            'php block without a region inside a tag' => ['<div <?php x ?> data-x={{ a > b }}>x</div>{{ c }}', [
                '<div>',
                '  "x"',
                '{{ c }}',
            ]],
            'noahs ark compares attribute values with normalized line endings' => ["<p><b class=\"x\r\ny\"><b class=\"x\ny\"><b class=\"x\ry\"><b class=\"x\r\ny\">1<div>2", [
                '<p>',
                '  <b>',
                '    <b>',
                '      <b>',
                '        <b>',
                '          "1"',
                '<div>',
                '  <b synthetic>',
                '    <b synthetic>',
                '      <b synthetic>',
                '        "2"',
            ]],
        ];
    }

    protected function dump($container, $depth = 0)
    {
        $pad = str_repeat('  ', $depth);
        $out = '';

        foreach ($container->children() as $child) {
            if ($child instanceof Element) {
                $name = $child->name() ?? '{dynamic}';
                $out .= $pad.'<'.$name.($child->isSynthetic() ? ' synthetic' : '').">\n";
                $out .= $this->dump($child, $depth + 1);
            } elseif ($child instanceof Text) {
                $out .= $pad.json_encode($child->content(), JSON_UNESCAPED_SLASHES)."\n";
            } elseif ($child instanceof AntlersNode) {
                $out .= $pad.$child->source()."\n";
            } elseif ($child instanceof OpaqueNode) {
                $out .= $pad.'#'.$child->type().' '.json_encode($child->source(), JSON_UNESCAPED_SLASHES)."\n";
            }
        }

        return $out;
    }
}
