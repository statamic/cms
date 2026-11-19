import DOMPurify from 'dompurify';

export default class PreviewHtml {
    constructor(html) {
        this.html = DOMPurify.sanitize(html ?? '');
    }
}
