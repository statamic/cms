<?php

return [
    'array' => [
        'config' => [
            'mode' => 'Dynamic mode gives the user control of the data while keyed mode does not.',
            'keys' => 'Set the array keys (variables) and optional labels.',
        ],
    ],
    'assets' => [
        'config' => [
            'allow_uploads' => 'Allow new file uploads.',
            'container' => 'Choose which asset container to use for this field.',
            'folder' => 'The folder to begin browsing in.',
            'max_files' => 'The maximum number of selectable assets.',
            'mode' => 'Choose your preferred layout style.',
            'restrict' => 'Prevent users from navigating to other folders.',
        ],
    ],
    'bard' => [
        'config' => [
            'allow_source' => 'Enable the option to view the HTML source code while writing.',
            'buttons' => 'Choose which buttons to show in the toolbar.',
            'container' => 'Choose which asset container to use for this field.',
            'fullscreen' => 'Enable the option to toggle into fullscreen mode',
            'link_noopener' => 'Set `rel="noopener` on all links.',
            'link_noreferrer' => 'Set `rel="noreferrer` on all links.',
            'reading_time' => 'Show estimated reading time at the bottom of the field.',
            'save_html' => 'Save HTML instead of structured data. This simplifies but limits control of your template markup.',
            'sets' => 'Sets are configurable blocks of fields that can be inserted anywhere in your Bard content.',
            'target_blank' => 'Set `target="_blank` on all links.',
            'toolbar_mode' => 'Choose which style of toolbar you prefer.',
        ]
    ],
    'checkboxes' => [
        'config' => [
            'inline' => 'Show the checkboxes in a row.',
            'options' => 'Set the array keys and their optional labels.',
        ],
    ],
    'code' => [
        'config' => [
            'indent_size' => 'Set your preferred indentation size (in spaces).',
            'indent_type' => 'Set your preferred type of indentation.',
            'key_map' => 'Choose preferred set of keyboard shortcuts.',
            'mode' => 'Choose language for syntax highlighting.',
            'theme' => 'Choose your prefered theme.',
        ],
    ],
    'color' => [
        'config' => [
            'color_modes' => 'Choose which color modes you want to pick between.',
            'default_color_mode' => 'Set the pre-selected color mode.',
            'lock_opacity' => 'Disables the alpha slider, preventing adjustments to opacity.',
            'swatches' => 'Pre-define colors that can be selected from a list.',
            'theme' => 'Choose between the classic and mini (simpler) color picker.',
        ],
    ],
    'date' => [
        'config' => [
            'columns' => 'Show multiple months at one time, in rows and columns',
            'earliest_date' => 'Set the earliest selectable date.',
            'format' => 'Optionally format the date string using [moment.js](https://momentjs.com/docs/#/displaying/format/).',
            'full_width' => 'Stretch the calender to use up the full width.',
            'inline' => 'Skip the dropdown input field and show the calendar directly.',
            'mode' => 'Choose between single or range mode (range disables time picker).',
            'rows' => 'Show multiple months at one time, in rows and columns',
            'time_enabled' => 'Enable the timepicker.',
            'time_required' => 'Require time _in addition_ to date.',
        ],
    ],
    'grid' => [
        'config' => [
            'add_row' => 'Set the label of the "Add Row" button.',
            'fields' => 'Each field becomes a column in the grid table.',
            'max_rows' => 'Set a maximum number of creatable rows.',
            'min_rows' => 'Set a minimum number of creatable rows.',
            'mode' => 'Choose your preferred layout style.',
            'reorderable' => 'Enable to allow row reordering.',
        ],
    ],
    'markdown' => [
        'config' => [
            'container' => 'Choose which asset container to use for this field.',
            'folder' => 'The folder to begin browsing in.',
            'restrict' => 'Prevent users from navigating to other folders.',
            'automatic_line_breaks' => 'Enables automatic line breaks.',
            'automatic_links' => 'Enables automatic linking of any URLs.',
            'escape_markup' => 'Escapes inline HTML markup (e.g. `<div>` to `&lt;div&gt;`).',
            'smartypants' => 'Automatically convert straight quotes into curly quotes, dashes into en/em-dashes, and other similar text transformations.',
            'parser' => 'The name of a customized Markdown parser. Leave blank for default.',
        ],
    ],
    'radio' => [
        'config' => [
            'inline' => 'Show the radio buttons in a row.',
            'options' => 'Set the array keys and their optional labels.',
        ],
    ],
    'range' => [
        'config' => [
            'append' => 'Add text to the end (right-side) of the slider.',
            'max' => 'The maximum, right-most value.',
            'min' => 'The minimum, left-most value.',
            'prepend' => 'Add text to the beginning (left-side) of the slider.',
            'step' => 'The minimum size between values.',
        ],
    ],
    'select' => [
        'config' => [
            'placeholder' => 'Set default, non-selectable placeholder text.',
            'options' => 'Set the keys and their optional labels.',
            'clearable' => 'Enable to allow deselecting your option.',
            'multiple' => 'Allow multiple selections.',
            'searchable' => 'Enable searching through possible options.',
            'taggable' => 'Allow adding new options in addition to pre-defined options',
            'push_tags' => 'Add newly created tags to the options list.',
            'cast_booleans' => 'Options with values of true and false will be saved as booleans.',
        ],
    ],
    'template' => [
        'config' => [
            'hide_partials' => 'Partials are rarely intended to be used as templates.',
        ],
    ],
    'text' => [
        'config' => [
            'append' => 'Add text after (to the right of) the text input.',
            'character_limit' => 'Set the maximum number of enterable characters.',
            'input_type' => 'Set the HTML5 input type.',
            'placeholder' => 'Set default placeholder text.',
            'prepend' => 'Add text before (to the left of) the text input.',
        ],
    ],
    'textarea' => [
        'config' => [
            'character_limit' => 'Set the maximum number of enterable characters.',
        ],
    ],
    'relationship' => [
        'config' => [
            'mode' => 'Choose your preferred UI style.'
        ]
    ],
];
