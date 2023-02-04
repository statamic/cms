const availableButtons = () => [
    { name: 'h1', text: __('Heading 1'), command: (editor, args) => editor.chain().focus().toggleHeading(args).run(), activeName: 'heading', args: { level: 1 }, html: '<i class="fa fa-header"><sup>1</sup></i>' },
    { name: 'h2', text: __('Heading 2'), command: (editor, args) => editor.chain().focus().toggleHeading(args).run(), activeName: 'heading', args: { level: 2 }, html: '<i class="fa fa-header"><sup>2</sup></i>' },
    { name: 'h3', text: __('Heading 3'), command: (editor, args) => editor.chain().focus().toggleHeading(args).run(), activeName: 'heading', args: { level: 3 }, html: '<i class="fa fa-header"><sup>3</sup></i>' },
    { name: 'h4', text: __('Heading 4'), command: (editor, args) => editor.chain().focus().toggleHeading(args).run(), activeName: 'heading', args: { level: 4 }, html: '<i class="fa fa-header"><sup>4</sup></i>' },
    { name: 'h5', text: __('Heading 5'), command: (editor, args) => editor.chain().focus().toggleHeading(args).run(), activeName: 'heading', args: { level: 5 }, html: '<i class="fa fa-header"><sup>5</sup></i>' },
    { name: 'h6', text: __('Heading 6'), command: (editor, args) => editor.chain().focus().toggleHeading(args).run(), activeName: 'heading', args: { level: 6 }, html: '<i class="fa fa-header"><sup>6</sup></i>' },
    { name: 'bold', text: __('Bold'), command: (editor) => editor.chain().focus().toggleBold().run(), icon: 'bold' },
    { name: 'italic', text: __('Italic'), command: (editor) => editor.chain().focus().toggleItalic().run(), icon: 'italic' },
    { name: 'underline', text: __('Underline'), command: (editor) => editor.chain().focus().toggleUnderline().run(), icon: 'underline' },
    { name: 'strikethrough', text: __('Strikethrough'), command: (editor) => editor.chain().focus().toggleStrike().run(), activeName: 'strike', icon: 'strikethrough' },
    { name: 'small', text: __('Small'), command: (editor) => editor.chain().focus().toggleSmall().run(), svg: 'small' },
    { name: 'unorderedlist', text: __('Unordered List'), command: (editor) => editor.chain().focus().toggleBulletList().run(), activeName: 'bulletList', icon: 'list-ul' },
    { name: 'orderedlist', text: __('Ordered List'), command: (editor) => editor.chain().focus().toggleOrderedList().run(), activeName: 'orderedList', icon: 'list-ol' },
    { name: 'removeformat', text: __('Remove Formatting'), command: (editor) => editor.chain().focus().clearNodes().unsetAllMarks().run(), icon: 'eraser' },
    { name: 'quote', text: __('Blockquote'), command: (editor) => editor.chain().focus().toggleBlockquote().run(), activeName: 'blockquote', icon: 'quote-right' },
    { name: 'superscript', text: __('Superscript'), command: (editor) => editor.chain().focus().toggleSuperscript().run(), icon: 'superscript' },
    { name: 'subscript', text: __('Subscript'), command: (editor) => editor.chain().focus().toggleSubscript().run(), icon: 'subscript' },
    { name: 'anchor', text: __('Link'), command: (editor) => editor.commands.setLink(), activeName: 'link', icon: 'link', component: 'LinkToolbarButton' },
    { name: 'table', text: __('Table'), command: (editor, args) => editor.commands.insertTable(args), args: { rowsCount: 3, colsCount: 3, withHeaderRow: false }, svg: 'add-table' },
    { name: 'image', text: __('Image'), command: (editor) => editor.commands.insertImage(), args: { src: '' }, icon: 'picture-o', condition: (config) => config.container },
    { name: 'code', text: __('Inline Code'), command: (editor) => editor.commands.toggleCode(), svg: 'angle-brackets-bold' },
    { name: 'codeblock', text: __('Code Block'), command: (editor) => editor.commands.toggleCodeBlock(), activeName: 'codeBlock', svg: 'code-block' },
    { name: 'horizontalrule', text: __('Horizontal Rule'), command: (editor) => editor.commands.setHorizontalRule(), activeName: 'horizontalRule', svg: 'range' },
    { name: 'alignleft', text: __('Align Left'), command: (editor) => editor.chain().focus().setTextAlign('left').run(), icon: 'align-left' },
    { name: 'aligncenter', text: __('Align Center'), command: (editor) => editor.chain().focus().setTextAlign('center').run(), icon: 'align-center' },
    { name: 'alignright', text: __('Align Right'), command: (editor) => editor.chain().focus().setTextAlign('right').run(), icon: 'align-right' },
    { name: 'alignjustify', text: __('Align Justify'), command: (editor) => editor.chain().focus().setTextAlign('justify').run(), icon: 'align-justify' },
];

const addButtonHtml = (buttons) => {
    return buttons.map(button => {
        if (!button.html) {
            button.html = button.icon ? `<i class="fa fa-${button.icon}"></i>` : false;
        }
        return button;
    });
}

export { availableButtons, addButtonHtml };
