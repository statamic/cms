<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Footnotes
    |--------------------------------------------------------------------------
    |
    | Words
    |
    | https://commonmark.thephpleague.com/2.4/extensions/footnotes/#configuration
    |
    */

    'footnotes' => [
        'backref_class'      => 'footnote-backref',
        'backref_symbol'     => '↩',
        'container_add_hr'   => true,
        'container_class'    => 'footnotes',
        'ref_class'          => 'footnote-ref',
        'ref_id_prefix'      => 'fnref:',
        'footnote_class'     => 'footnote',
        'footnote_id_prefix' => 'fn:',
    ],

    /*
    |--------------------------------------------------------------------------
    | Heading Permalinks
    |--------------------------------------------------------------------------
    |
    | Words
    |
    | https://commonmark.thephpleague.com/2.4/extensions/footnotes/#configuration
    |
    */

    'heading_permalinks' => [
        'enable' => false,
        'config' => [
            'html_class' => 'heading-permalink',
            'id_prefix' => 'content',
            'apply_id_to_heading' => false,
            'heading_class' => '',
            'fragment_prefix' => 'content',
            'insert' => 'before',
            'min_heading_level' => 1,
            'max_heading_level' => 3,
            'title' => 'Permalink',
            'symbol' => '#',
            'aria_hidden' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Table of Contents
    |--------------------------------------------------------------------------
    |
    | Words
    |
    | https://commonmark.thephpleague.com/2.4/extensions/table-of-contents/#configuration
    |
    */

    'table_of_contents' => [
        'enable' => false,
        'config' => [
            'html_class' => 'table-of-contents',
            'position' => 'top',
            'style' => 'bullet',
            'min_heading_level' => 1,
            'max_heading_level' => 3,
            'normalize' => 'relative',
            'placeholder' => null,
        ],
    ],




];
