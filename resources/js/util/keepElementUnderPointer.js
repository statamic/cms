export function nearestScrollable(el) {
    let node = el.parentElement;

    while (node && node !== document.body) {
        const { overflowY } = getComputedStyle(node);
        if (
            (overflowY === 'auto' || overflowY === 'scroll' || overflowY === 'overlay') &&
            node.scrollHeight > node.clientHeight + 1
        ) {
            return node;
        }
        node = node.parentElement;
    }

    return document.scrollingElement || document.documentElement;
}

export function keepElementUnderPointer(el, mutate) {
    if (!el) {
        mutate();
        return;
    }

    const before = el.getBoundingClientRect().top;
    mutate();
    const delta = el.getBoundingClientRect().top - before;
    if (delta) {
        nearestScrollable(el).scrollTop += delta;
    }
}
