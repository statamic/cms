// This file is adapted from https://github.com/ePages-de/scribe-plugin-enhanced-link-tooltip

module.exports = function (vue) {

    // http://stackoverflow.com/a/25206094/1317451
    function findClosestParent(startElement, fn) {
        var parent = startElement.parentElement;
        if (!parent) {
            return undefined;
        }
        return fn(parent) ? parent : findClosestParent(parent, fn);
    }

    return function (scribe) {

        const showTooltipForCreatingLink = () => {
            var selection = new scribe.api.Selection();

            addBlurListener();

            vue.resetState();
            vue.scribe = scribe;
            vue.command = linkTooltipCommand;

            vue.createCallback = (url) => {
                getSelection().removeAllRanges();
                getSelection().addRange(selection.range);
                scribe.api.SimpleCommand.prototype.execute.call(linkTooltipCommand, url);
                scribe.el.focus();

                // Get the anchor that was just created so vue will have a reference to it.
                // The focusNode property appeared to be the only consistent thing between browsers.
                // It is the text at the end of the selection.
                return findClosestParent(selection.selection.focusNode, node => node.nodeName === 'A');
            }

            vue.edit();
            repositionTooltip();
        }

        const addBlurListener = () => {
            const listener = (e) => {
                const isTooltipUiElement = !!findClosestParent(e.target, el => el === vue.$el);
                if (isTooltipUiElement) {
                    return true; // let blur event pass through
                }
                document.removeEventListener('mouseup', listener);
            };
            document.addEventListener('mouseup', listener);
        }

        const showTooltipForExistingLink = (anchor) => {
            addBlurListener();

            vue.scribe = scribe;
            vue.anchorElement = anchor;
            vue.isEditing = false;
            repositionTooltip();
        }

        const repositionTooltip = (coords) => {
            const { top, left } = getCoordinates();
            vue.positionTop = top;
            vue.positionLeft = left;
        };

        const hideTooltip = () => {
            vue.positionTop = '-999em';
            vue.positionleft = '-999em';
        };

        const getCoordinates = () => {
            const selection = new scribe.api.Selection();

            // calculate position
            const selectionRects = (function () {
                let rects = selection.range.getClientRects();
                if (!rects.length) {
                    rects = selection.range.startContainer.getClientRects();
                }
                return rects;
            }());

            const scribeParentRect = scribe.el.parentNode.parentNode.parentNode.getBoundingClientRect();
            const biggestSelection = [].reduce.call(selectionRects, function (biggest, rect) {
                return rect.width >= biggest.width ? {
                    rect: rect,
                    width: rect.width
                } : {
                    rect: biggest.rect,
                    width: biggest.width
                };
            }, {
                width: 0
            });

            const left = biggestSelection.rect ? biggestSelection.rect.left : 0;
            const top = selectionRects.length ? selectionRects[selectionRects.length - 1].bottom : 0;
            const tooltipWidth = parseFloat(getComputedStyle(vue.$el).width);
            const offsetLeft = left - scribeParentRect.left - tooltipWidth / 2;
            const correctedOffsetLeft = offsetLeft < 0 ? 0 : Math.min(offsetLeft, scribeParentRect.width - tooltipWidth - 10);

            return {
                top: top - scribeParentRect.top + 'px',
                left: correctedOffsetLeft + 'px'
            };
        };

        const linkTooltipCommand = new scribe.api.Command('createLink');
        scribe.commands.linkTooltip = linkTooltipCommand;

        // Whether the selection is considered a link. Makes the toolbar button active.
        linkTooltipCommand.queryState = function () {
            const selection = new scribe.api.Selection();
            const anchor = selection.getContaining(node => node.nodeName === 'A');
            const isInsideAnchor = !!anchor;
            const containsAnchor = !!selection.range.cloneContents().querySelector('a');

            // Since queryState is called all the time, even when navigating using
            // the keyboard, it's a good opportunity to reposition the tooltip.
            (isInsideAnchor && selection.isInScribe())
                ? showTooltipForExistingLink(anchor)
                : hideTooltip();

            return isInsideAnchor || containsAnchor;
        };

        // Whether the selection can be made into a link. Enables the toolbar button.
        linkTooltipCommand.queryEnabled = function () {
            const selection = new scribe.api.Selection();
            const anchor = selection.getContaining(node => node.nodeName === 'A');
            const isInsideAnchor = !!anchor;
            const containsAnchor = !!selection.range.cloneContents().querySelector('a');
            return !isInsideAnchor && !containsAnchor;
        };

        // When the command is executed. ie. the toolbar button is clicked, or a keyboard shortcut is used.
        linkTooltipCommand.execute = function () {
            // This is needed since scribe toolbar executes the command on mousedown
            // (see https://github.com/guardian/scribe-plugin-toolbar/pull/18)
            const handler = () => {
                document.removeEventListener('click', handler);
                showTooltipForCreatingLink();
            };
            document.addEventListener('click', handler);
        };
    };
};
