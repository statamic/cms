export default function () {

    return function (scribe) {

        function findBlockContainer(node) {
            while (node && !scribe.node.isBlockElement(node)) {
                node = node.parentNode;
            }

            return node;
        }

        function removeQuoteCharacter(el) {
            const firstChild = el.childNodes[0];

            if (firstChild.nodeName === '#text') {
                firstChild.textContent = firstChild.textContent.substr(2);
                return;
            }

            removeQuoteCharacter(firstChild);
        }

        function getBlockFromSelection(selection) {
            selection = selection || new scribe.api.Selection();
            const container = selection.range.commonAncestorContainer;
            return findBlockContainer(container);
        }

        function input() {
            const selection = new scribe.api.Selection();
            const block = getBlockFromSelection(selection);

            if (block.nodeName !== 'P') {
                return; // This should only work inside paragraphs.
            }

            if (block.parentNode !== scribe.el) {
                return; // Bail if the p tag isn't top level (like, inside another blockquote)
            }

            if (! block.textContent.substr(0, 2).match(/\>\s/)) {
                return; // It's not the blockquote, we're done.
            }

            const range = selection.range;
            range.selectNode(block);
            selection.selection.removeAllRanges();
            selection.selection.addRange(range);

            scribe.transactionManager.run(function() {
                scribe.getCommand('blockquote').execute();
                removeQuoteCharacter(getBlockFromSelection());
                getSelection().collapseToStart();
            });
        }

        scribe.el.addEventListener('keyup', input);
    };
};
