const availableButtons = () => [
    { name: 'bold', text: __('Bold'), command: (editor) => editor.toggleInline('bold'), svg: 'ui/text-bold' },
    { name: 'italic', text: __('Italic'), command: (editor) => editor.toggleInline('italic'), svg: 'ui/text-italic' },
    { name: 'strikethrough', text: __('Strikethrough'), command: (editor) => editor.toggleInline('strikethrough'), svg: 'ui/text-strike-through' },
    { name: 'unorderedlist', text: __('Unordered List'), command: (editor) => editor.toggleLine('unordered-list'), svg: 'ui/list-ul' },
    { name: 'orderedlist', text: __('Ordered List'), command: (editor) => editor.toggleLine('ordered-list'), svg: 'ui/list-ol' },
    { name: 'quote', text: __('Blockquote'), command: (editor) => editor.toggleLine('quote'), svg: 'ui/quote' },
    { name: 'link', text: __('Insert Link'), command: (editor) => editor.insertLink(''), svg: 'ui/insert-link' },
    { name: 'table', text: __('Table'), command: (editor) => editor.insertTable(), svg: 'ui/add-table' },
    { name: 'image', text: __('Insert Image'), command: (editor) => editor.insertImage(), svg: 'ui/insert-image' },
    { name: 'asset', text: __('Insert Asset'), command: (editor) => editor.addAsset(), svg: 'ui/asset-folder', condition: (config) => config.container },
    { name: 'code', text: __('Inline Code'), command: (editor) => editor.toggleInline('code'), svg: 'ui/code-inline' },
    { name: 'codeblock', text: __('Code Block'), command: (editor) => editor.toggleBlock('code'), svg: 'ui/code-block' },
];

export { availableButtons };
