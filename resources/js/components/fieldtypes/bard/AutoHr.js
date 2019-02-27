export default function () {

    return function (scribe) {

        function findBlockContainer(node) {
            while (node && !scribe.node.isBlockElement(node)) {
                node = node.parentNode;
            }

            return node;
        }

        function input() {
            const selection = new scribe.api.Selection();
            const container = selection.range.commonAncestorContainer;
            const block = findBlockContainer(container);

            if (block.nodeName !== 'P') {
                return; // This should only work inside paragraphs.
            }

            if (block.textContent !== '---') {
                return; // It's not the hr, we're done.
            }

            const range = selection.range;
            range.selectNode(block);
            selection.selection.removeAllRanges();
            selection.selection.addRange(range);

            scribe.getCommand('insertHorizontalRule').execute();
        }

        scribe.el.addEventListener('keyup', input);
    };
};
