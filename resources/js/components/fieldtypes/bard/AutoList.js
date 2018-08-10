/**
 * Adapted from medium-editor-autolist
 * https://github.com/varun-raj/medium-editor-autolist/blob/master/dist/autolist.js
 */
export default MediumEditor.Extension.extend({
    name: 'autolist',
    init: function () {
        this.subscribe('editableInput', this.onInput.bind(this));
        this.subscribe('editablePaste', this.onPaste.bind(this));
    },
    onInput: function (keyPressEvent) {
        if (! this.base.getFocusedElement()) return;

        var list_start = this.base.getSelectedParentElement().textContent;
        if (/^1\.\s$/.test(list_start)) {
            this.base.execAction('delete');
            this.base.execAction('delete');
            this.base.execAction('delete');
            this.base.execAction('insertorderedlist');
        }
        else if (/^[\*\-]\s$/.test(list_start)) {
            this.base.execAction('delete');
            this.base.execAction('delete');
            this.base.execAction('insertunorderedlist');
        }
    },
    onPaste: function (pasteEvent) {
        const field = pasteEvent.target
        Array.from(field.children).forEach(el => replaceListItem(el));

        // Save the selection *after* the replacements have been done because the list prefixes will be removed.
        this.base.saveSelection();

        let els = flattenGroups(wrapListItems(groupElements(Array.from(field.children))));
        els.forEach(el => field.appendChild(el));
        this.base.checkContentChanged();

        this.base.restoreSelection();
    }
});

/**
 * Group list and non-list items together.
 *
 * Iterate over all the DOM elements, when a list item is encountered after a non list-item, start a new group.
 * Visa-verse, when a non-list item is encountered after a list item, start a new group.
 *
 * eg. [[p, p], [li, li, li], [p], [li]]
 */
function groupElements(els) {
    let groups = [[]];
    let currentGroup = 0;
    let prevWasListItem = false;

    for (let i = 0; i < els.length; i++) {
        const el = els[i];
        const currentIsListItem = el.nodeName === 'LI';
        const hasChanged = (prevWasListItem && !currentIsListItem) || (!prevWasListItem && currentIsListItem);

        if (hasChanged) {
            currentGroup++;
            groups[currentGroup] = [];
        }

        prevWasListItem = currentIsListItem;
        groups[currentGroup].push(el);
    }

    return groups.filter(group => group.length);
}

/**
 * Given an array of element groups, wrap any list-item groups in either a ul or ol
 */
function wrapListItems(groups) {
    return groups.map(group => {
        let firstEl = group[0];

        // If it's not a list item, it's fine as it is.
        if (firstEl.nodeName !== 'LI') return group;

        let wrapper = document.createElement(firstEl.dataset.liType);
        group.forEach(li => {
            delete li.dataset.liType; // Remove the temporary thing.
            wrapper.appendChild(li);
        });

        return [wrapper];
    });
}

function flattenGroups(groups) {
    let els = [];
    groups.forEach(group => {
        group.forEach(el => els.push(el));
    });
    return els;
}

/**
 * Replace a DOM element that has a list-item text prefix with an actual <li> element.
 */
function replaceListItem(el) {
    const type = listItemType(el);
    if (! type) return; // If it's not a list item, we don't want to replace it.

    let newEl = document.createElement('li');

    // Set whether it belongs in a ul or ol depending on the prefix, and store it in the DOM's datalist for later.
    const regex = new RegExp(type === 'ul' ? /^([\*\-]\s)/ : /^(\d+\.\s)/);
    newEl.textContent = el.textContent.trim().replace(regex, '');
    newEl.dataset.liType = type;

    replaceInDom(el, newEl);
}

/**
 * Determine whether a given element should be in a ul or ol
 */
function listItemType(el) {
    const content = el.textContent.trim();

    if (/^[\*\-]\s/.test(content)) {
        return 'ul';
    } else if (/^\d+\.\s/.test(content)) {
        return 'ol';
    }

    return false;
}

/**
 * Replace an element in the DOM with another.
 */
function replaceInDom(oldEl, newEl) {
    oldEl.parentNode.insertBefore(newEl, oldEl.nextSibling);
    oldEl.parentNode.removeChild(oldEl);
}
