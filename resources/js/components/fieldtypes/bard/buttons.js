const availableButtons = () => [
    { name: 'h1', text: __('cp.heading_1'), command: 'h1', html: '<i class="fa fa-header"><sup>1</sup></i>' },
    { name: 'h2', text: __('cp.heading_2'), command: 'h2', html: '<i class="fa fa-header"><sup>2</sup></i>' },
    { name: 'h3', text: __('cp.heading_3'), command: 'h3', html: '<i class="fa fa-header"><sup>3</sup></i>' },
    { name: 'h4', text: __('cp.heading_4'), command: 'h4', html: '<i class="fa fa-header"><sup>4</sup></i>' },
    { name: 'h5', text: __('cp.heading_5'), command: 'h5', html: '<i class="fa fa-header"><sup>5</sup></i>' },
    { name: 'h6', text: __('cp.heading_6'), command: 'h6', html: '<i class="fa fa-header"><sup>6</sup></i>' },
    { name: 'bold', text: __('cp.bold'), command: 'bold', icon: 'bold' },
    { name: 'italic', text: __('cp.italic'), command: 'italic', icon: 'italic' },
    { name: 'underline', text: __('cp.underline'), command: 'underline', icon: 'underline' },
    { name: 'strikethrough', text: __('cp.strikethrough'), command: 'strikeThrough', icon: 'strikethrough' },
    { name: 'unorderedlist', text: __('cp.unordered_list'), command: 'insertUnorderedList', icon: 'list-ul' },
    { name: 'orderedlist', text: __('cp.ordered_list'), command: 'insertOrderedList', icon: 'list-ol' },
    { name: 'removeformat', text: __('cp.remove_formatting'), command: 'removeFormat', icon: 'eraser' },
    { name: 'quote', text: __('cp.blockquote'), command: 'blockquote', icon: 'quote-right' },
    { name: 'superscript', text: __('cp.superscript'), command: 'superscript', icon: 'superscript' },
    { name: 'subscript', text: __('cp.subscript'), command: 'subscript', icon: 'subscript' },
    { name: 'anchor', text: __('cp.add_link'), command: 'linkTooltip', icon: 'link' },
    { name: 'assets', text: __('cp.link_to_asset'), command: 'insertAsset', icon: 'picture-o', condition: (config) => config.container },
    { name: 'code', text: __('cp.code'), command: 'code', icon: 'code' },
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
