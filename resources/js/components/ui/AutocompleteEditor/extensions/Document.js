import Document from '@tiptap/extension-document';

export const DocumentBlock = Document;

export const DocumentInline = Document.extend({
    content: 'paragraph',
});
