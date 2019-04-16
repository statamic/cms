const availableButtons = () => [
    { name: 'h1', text: __('Heading 1'), command: 'heading', args: { level: 1 }, html: '<i class="fa fa-header"><sup>1</sup></i>' },
    { name: 'h2', text: __('Heading 2'), command: 'heading', args: { level: 2 }, html: '<i class="fa fa-header"><sup>2</sup></i>' },
    { name: 'h3', text: __('Heading 3'), command: 'heading', args: { level: 3 }, html: '<i class="fa fa-header"><sup>3</sup></i>' },
    { name: 'h4', text: __('Heading 4'), command: 'heading', args: { level: 4 }, html: '<i class="fa fa-header"><sup>4</sup></i>' },
    { name: 'h5', text: __('Heading 5'), command: 'heading', args: { level: 5 }, html: '<i class="fa fa-header"><sup>5</sup></i>' },
    { name: 'h6', text: __('Heading 6'), command: 'heading', args: { level: 6 }, html: '<i class="fa fa-header"><sup>6</sup></i>' },
    { name: 'bold', text: __('Bold'), command: 'bold', icon: 'bold' },
    { name: 'italic', text: __('Italic'), command: 'italic', icon: 'italic' },
    { name: 'underline', text: __('Underline'), command: 'underline', icon: 'underline' },
    { name: 'strikethrough', text: __('Strikethrough'), command: 'strike', icon: 'strikethrough' },
    { name: 'unorderedlist', text: __('Unordered List'), command: 'bullet_list', icon: 'list-ul' },
    { name: 'orderedlist', text: __('Ordered List'), command: 'ordered_list', icon: 'list-ol' },
    // { name: 'removeformat', text: __('Remove Formatting'), command: 'removeFormat', icon: 'eraser' },
    { name: 'quote', text: __('Blockquote'), command: 'blockquote', icon: 'quote-right' },
    // { name: 'superscript', text: __('Superscript'), command: 'superscript', icon: 'superscript' },
    // { name: 'subscript', text: __('Subscript'), command: 'subscript', icon: 'subscript' },
    { name: 'anchor', text: __('Add Link'), command: 'link', icon: 'link', component: 'LinkToolbarButton' },
    // { name: 'assets', text: __('Link to Asset'), command: 'insertAsset', icon: 'picture-o', condition: (config) => config.container },
    { name: 'code', text: __('Code'), command: 'code', icon: 'code' },
    { name: 'codeblock', text: __('Code Block'), command: 'code_block', icon: 'code' },
];

const addButtonHtml = (buttons) => {
    return buttons.map(button => {
        if (!button.html) {
            button.html = button.icon ? `<i class="fa fa-${button.icon}"></i>` : button.text;
        }
        return button;
    });
}

export { availableButtons, addButtonHtml };
