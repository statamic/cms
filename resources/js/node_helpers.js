
/**
 * @see https://developer.mozilla.org/en-US/docs/Web/API/Document_Object_Model/Whitespace#whitespace_helper_functions
 *
 * Throughout, whitespace is defined as one of the characters
 *  "\t" TAB \u0009
 *  "\n" LF  \u000A
 *  "\r" CR  \u000D
 *  " "  SPC \u0020
 *
 * This does not use JavaScript's "\s" because that includes non-breaking
 * spaces (and also some other characters).
 */

/**
 * Determine whether a node's text content is entirely whitespace.
 *
 * @param nod  A node implementing the |CharacterData| interface (i.e.,
 *             a |Text|, |Comment|, or |CDATASection| node
 * @return     True if all of the text content of |nod| is whitespace,
 *             otherwise false.
 */
export function is_all_ws(nod) {
    return !/[^\t\n\r ]/.test(nod.textContent);
}

/**
 * Determine if a node should be ignored by the iterator functions.
 *
 * @param nod  An object implementing the DOM1 |Node| interface.
 * @return     true if the node is:
 *                1) A |Text| node that is all whitespace
 *                2) A |Comment| node
 *             and otherwise false.
 */
export function is_ignorable(nod) {
    return (
        nod.nodeType === 8 || // A comment node
        (nod.nodeType === 3 && is_all_ws(nod))
    ); // a text node, all ws
}

/**
 * Version of |previousSibling| that skips nodes that are entirely
 * whitespace or comments. (Normally |previousSibling| is a property
 * of all DOM nodes that gives the sibling node, the node that is
 * a child of the same parent, that occurs immediately before the
 * reference node.)
 *
 * @param sib  The reference node.
 * @return     Either:
 *               1) The closest previous sibling to |sib| that is not
 *                  ignorable according to |is_ignorable|, or
 *               2) null if no such node exists.
 */
export function node_before(sib) {
    while ((sib = sib.previousSibling)) {
        if (!is_ignorable(sib)) {
            return sib;
        }
    }
    return null;
}

/**
 * Version of |nextSibling| that skips nodes that are entirely
 * whitespace or comments.
 *
 * @param sib  The reference node.
 * @return     Either:
 *               1) The closest next sibling to |sib| that is not
 *                  ignorable according to |is_ignorable|, or
 *               2) null if no such node exists.
 */
export function node_after(sib) {
    while ((sib = sib.nextSibling)) {
        if (!is_ignorable(sib)) {
            return sib;
        }
    }
    return null;
}

/**
 * Version of |lastChild| that skips nodes that are entirely
 * whitespace or comments. (Normally |lastChild| is a property
 * of all DOM nodes that gives the last of the nodes contained
 * directly in the reference node.)
 *
 * @param sib  The reference node.
 * @return     Either:
 *               1) The last child of |sib| that is not
 *                  ignorable according to |is_ignorable|, or
 *               2) null if no such node exists.
 */
export function last_child(par) {
    let res = par.lastChild;
    while (res) {
        if (!is_ignorable(res)) {
            return res;
        }
        res = res.previousSibling;
    }
    return null;
}

/**
 * Version of |firstChild| that skips nodes that are entirely
 * whitespace and comments.
 *
 * @param sib  The reference node.
 * @return     Either:
 *               1) The first child of |sib| that is not
 *                  ignorable according to |is_ignorable|, or
 *               2) null if no such node exists.
 */
export function first_child(par) {
    let res = par.firstChild;
    while (res) {
        if (!is_ignorable(res)) {
            return res;
        }
        res = res.nextSibling;
    }
    return null;
}

/**
 * Version of |data| that doesn't include whitespace at the beginning
 * and end and normalizes all whitespace to a single space. (Normally
 * |data| is a property of text nodes that gives the text of the node.)
 *
 * @param txt  The text node whose data should be returned
 * @return     A string giving the contents of the text node with
 *             whitespace collapsed.
 */
export function data_of(txt) {
    let data = txt.textContent;
    data = data.replace(/[\t\n\r ]+/g, " ");
    if (data[0] === " ") {
        data = data.substring(1, data.length);
    }
    if (data[data.length - 1] === " ") {
        data = data.substring(0, data.length - 1);
    }
    return data;
}

/**
 * Pass this.$el from a vue component to this function.
 * We will grab the first non-ignorable element.
 *
 * @param element the root element from Vue: this.$el
 * @return returns an element or null if there's no node.
 */
export function vue_element(element) {
    while (element && is_ignorable(element)) {
        element = element.nextSibling
    }

    return element;
}