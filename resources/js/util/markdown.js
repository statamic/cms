import { marked } from 'marked';

export default function (markdown, options = {}) {
    const renderer = new marked.Renderer();

    if (options.openLinksInNewTabs) {
        renderer.link = function(href, title, text) {
            return marked.Renderer.prototype.link
                .call(this, href, title, text)
                .replace("<a", "<a target='_blank' ");
        };
    }

    return marked.parse(markdown, { renderer });
}
