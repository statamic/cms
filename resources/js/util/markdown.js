import { marked } from 'marked';
import DOMPurify from 'dompurify';

export default function (markdown, options = {}) {
    if (!markdown) return '';

    const renderer = new marked.Renderer();

    if (options.openLinksInNewTabs) {
        renderer.link = function(href, title, text) {
            return marked.Renderer.prototype.link
                .call(this, href, title, text)
                .replace("<a", "<a target='_blank' ");
        };
    }

    return DOMPurify.sanitize(
        marked.parse(markdown, { renderer })
    );
}
