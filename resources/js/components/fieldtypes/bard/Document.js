import Document from '@tiptap/extension-document'

export const DocumentBlock = Document.extend({

    content: '(block | root)+'

})

export const DocumentInline = Document.extend({

    content: 'paragraph',

});
