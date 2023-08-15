const availableButtons = () => [
    { name: 'h1', text: __('Heading 1'), command: (editor, args) => editor.chain().focus().toggleHeading(args).run(), activeName: 'heading', args: { level: 1 }, svg: 'h1' },
    { name: 'h2', text: __('Heading 2'), command: (editor, args) => editor.chain().focus().toggleHeading(args).run(), activeName: 'heading', args: { level: 2 }, svg: 'h2' },
    { name: 'h3', text: __('Heading 3'), command: (editor, args) => editor.chain().focus().toggleHeading(args).run(), activeName: 'heading', args: { level: 3 }, svg: 'h3' },
    { name: 'h4', text: __('Heading 4'), command: (editor, args) => editor.chain().focus().toggleHeading(args).run(), activeName: 'heading', args: { level: 4 }, svg: 'h4' },
    { name: 'h5', text: __('Heading 5'), command: (editor, args) => editor.chain().focus().toggleHeading(args).run(), activeName: 'heading', args: { level: 5 }, svg: 'h5' },
    { name: 'h6', text: __('Heading 6'), command: (editor, args) => editor.chain().focus().toggleHeading(args).run(), activeName: 'heading', args: { level: 6 }, svg: 'h6' },
    { name: 'bold', text: __('Bold'), command: (editor) => editor.chain().focus().toggleBold().run(), svg: 'text-bold' },
    { name: 'italic', text: __('Italic'), command: (editor) => editor.chain().focus().toggleItalic().run(), svg: 'text-italic' },
    { name: 'underline', text: __('Underline'), command: (editor) => editor.chain().focus().toggleUnderline().run(), svg: 'text-underline' },
    { name: 'strikethrough', text: __('Strikethrough'), command: (editor) => editor.chain().focus().toggleStrike().run(), activeName: 'strike', svg: 'text-strike-through' },
    { name: 'small', text: __('Small'), command: (editor) => editor.chain().focus().toggleSmall().run(), svg: 'text-small' },
    { name: 'unorderedlist', text: __('Unordered List'), command: (editor) => editor.chain().focus().toggleBulletList().run(), activeName: 'bulletList', svg: 'list-ul' },
    { name: 'orderedlist', text: __('Ordered List'), command: (editor) => editor.chain().focus().toggleOrderedList().run(), activeName: 'orderedList', svg: 'list-ol' },
    { name: 'removeformat', text: __('Remove Formatting'), command: (editor) => editor.chain().focus().clearNodes().unsetAllMarks().run(), svg: 'eraser' },
    { name: 'quote', text: __('Blockquote'), command: (editor) => editor.chain().focus().toggleBlockquote().run(), activeName: 'blockquote', svg: 'quote' },
    { name: 'superscript', text: __('Superscript'), command: (editor) => editor.chain().focus().toggleSuperscript().run(), svg: 'superscript' },
    { name: 'subscript', text: __('Subscript'), command: (editor) => editor.chain().focus().toggleSubscript().run(), svg: 'subscript' },
    { name: 'anchor', text: __('Link'), command: (editor) => editor.commands.setLink(), activeName: 'link', svg: 'insert-link', component: 'LinkToolbarButton' },
    { name: 'table', text: __('Table'), command: (editor, args) => editor.commands.insertTable(args), args: { rowsCount: 3, colsCount: 3, withHeaderRow: false }, svg: 'add-table' },
    { name: 'image', text: __('Image'), command: (editor) => editor.commands.insertImage(), args: { src: '' }, svg: 'insert-image', condition: (config) => config.container },
    { name: 'code', text: __('Inline Code'), command: (editor) => editor.commands.toggleCode(), svg: 'code-inline' },
    { name: 'codeblock', text: __('Code Block'), command: (editor) => editor.commands.toggleCodeBlock(), activeName: 'codeBlock', svg: 'code-block' },
    { name: 'horizontalrule', text: __('Horizontal Rule'), command: (editor) => editor.commands.setHorizontalRule(), activeName: 'horizontalRule', svg: 'subtract' },
    { name: 'alignleft', text: __('Align Left'), command: (editor) => editor.chain().focus().setTextAlign('left').run(), svg: 'paragraph-align-left' },
    { name: 'aligncenter', text: __('Align Center'), command: (editor) => editor.chain().focus().setTextAlign('center').run(), svg: 'paragraph-align-center' },
    { name: 'alignright', text: __('Align Right'), command: (editor) => editor.chain().focus().setTextAlign('right').run(), svg: 'paragraph-align-right' },
    { name: 'alignjustify', text: __('Align Justify'), command: (editor) => editor.chain().focus().setTextAlign('justify').run(), svg: 'paragraph-align-justified' },
];

const addButtonHtml = (buttons) => {
    return buttons.map(button => {
        return button;
    });
}

export { availableButtons, addButtonHtml };
