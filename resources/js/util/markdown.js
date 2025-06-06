import { marked } from 'marked';

export default function renderMarkdown(markdown) {
    let renderer = new marked.Renderer();

    renderer.link = function (href, title, text) {
        let link = marked.Renderer.prototype.link.call(this, href, title, text);

        return link.replace("<a","<a target='_blank' ");
    };

    marked.setOptions({
        renderer: renderer
    });

    return marked(markdown);
}
