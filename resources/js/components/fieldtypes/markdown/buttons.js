const availableButtons = () => [
    { name: 'bold', text: __('Bold'), command: (editor) => editor.toggleInline('bold'), svg: 'text-bold' },
    { name: 'italic', text: __('Italic'), command: (editor) => editor.toggleInline('italic'), svg: 'text-italic' },
    {
        name: 'strikethrough',
        text: __('Strikethrough'),
        command: (editor) => editor.toggleInline('strikethrough'),
        svg: 'text-strike-through',
    },
    {
        name: 'unorderedlist',
        text: __('Unordered List'),
        command: (editor) => editor.toggleLine('unordered-list'),
        svg: 'list-ul',
    },
    {
        name: 'orderedlist',
        text: __('Ordered List'),
        command: (editor) => editor.toggleLine('ordered-list'),
        svg: 'list-ol',
    },
    { name: 'quote', text: __('Blockquote'), command: (editor) => editor.toggleLine('quote'), svg: 'quote' },
    { name: 'link', text: __('Insert Link'), command: (editor) => editor.insertLink(''), svg: 'insert-link' },
    { name: 'table', text: __('Table'), command: (editor) => editor.insertTable(), svg: 'add-table' },
    { name: 'image', text: __('Insert Image'), command: (editor) => editor.insertImage(), svg: 'insert-image' },
    {
        name: 'asset',
        text: __('Insert Asset'),
        command: (editor) => editor.addAsset(),
        svg: 'asset-folder',
        condition: (config) => config.container,
    },
    { name: 'code', text: __('Inline Code'), command: (editor) => editor.toggleInline('code'), svg: 'code-inline' },
    { name: 'codeblock', text: __('Code Block'), command: (editor) => editor.toggleBlock('code'), svg: 'code-block' },
];

export { availableButtons };
