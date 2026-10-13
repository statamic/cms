import { createHighlightOverlay } from './FieldHighlights.js';

export const fieldHighlightDuration = 2000;

export const fieldManifestId = 'statamic-preview-fields';

const fieldAttribute = 'data-statamic-field';
const markerComment = /^(\/?)statamic:field:(\d+)$/;

export function readFieldManifest(doc) {
    let manifest;

    try {
        manifest = JSON.parse(doc.getElementById(fieldManifestId)?.textContent ?? 'null');
    } catch {
        return null;
    }

    return manifest?.owner && manifest.fields && manifest.markers ? manifest : null;
}

function pathOf(field) {
    return field.path.join('.');
}

function createEntry(fields, element, range, depth) {
    const rects = () => (range ? Array.from(range.getClientRects()) : [element.getBoundingClientRect()]);

    return {
        fields,
        element,
        depth,
        rects,
        boundingRect: () => (range ?? element).getBoundingClientRect(),
        contains: (x, y) => rects().some(rect => x >= rect.left && x <= rect.right && y >= rect.top && y <= rect.bottom),
    };
}

export function createFieldIndex(doc, manifest) {
    const byElement = new WeakMap();
    const byPath = new Map();

    function fieldsFor(marker) {
        return (manifest.markers[marker] ?? [])
            .map(id => manifest.fields[id])
            .filter(field => {
                return Array.isArray(field?.path)
                    && field.reference === manifest.owner.reference
                    && field.locale === manifest.owner.locale;
            });
    }

    function add(marker, element, range = null, depth = 0) {
        const fields = fieldsFor(marker);
        if (!fields.length || !element) return;

        const entry = createEntry(fields, element, range, depth);

        byElement.set(element, [...(byElement.get(element) ?? []), entry]);

        for (const field of fields) {
            const path = pathOf(field);
            byPath.set(path, [...(byPath.get(path) ?? []), entry]);
        }
    }

    function walk() {
        const win = doc.defaultView;
        const walker = doc.createTreeWalker(doc.body, win.NodeFilter.SHOW_ELEMENT | win.NodeFilter.SHOW_COMMENT);
        const open = new Map();
        let depth = 0;

        while (walker.nextNode()) {
            const node = walker.currentNode;

            if (node.nodeType === win.Node.ELEMENT_NODE) {
                if (node.hasAttribute(fieldAttribute)) add(node.getAttribute(fieldAttribute), node);
                continue;
            }

            const match = node.data.match(markerComment);
            if (!match) continue;

            const [, closing, marker] = match;

            if (!closing) {
                open.set(marker, { node, depth: ++depth });
                continue;
            }

            const start = open.get(marker);
            open.delete(marker);
            depth = Math.max(0, depth - 1);

            if (!start || start.node.parentNode !== node.parentNode) continue;

            const range = doc.createRange();
            range.setStartAfter(start.node);
            range.setEndBefore(node);
            add(marker, node.parentElement, range, start.depth);
        }
    }

    if (manifest && doc.body) walk();

    return {
        forPath(path) {
            return byPath.get(path) ?? [];
        },

        at(x, y, target) {
            for (let element = target; element; element = element.parentElement) {
                const hits = (byElement.get(element) ?? []).filter(entry => entry.fields.length === 1 && entry.contains(x, y));

                if (hits.length) return hits.sort((a, b) => b.depth - a.depth)[0];
            }

            return null;
        },
    };
}

function scrollContainersOf(doc, element) {
    const win = doc.defaultView;
    const containers = [];

    for (let el = element; el && el !== doc.body; el = el.parentElement) {
        if (el.scrollHeight > el.clientHeight && /auto|scroll|hidden/.test(win.getComputedStyle(el).overflowY)) {
            containers.push(el);
        }
    }

    return containers;
}

function isInView(doc, entry, rect) {
    const win = doc.defaultView;

    if (rect.top < 0 || rect.bottom > win.innerHeight || rect.left < 0 || rect.right > win.innerWidth) return false;

    return scrollContainersOf(doc, entry.element).every(container => {
        const bounds = container.getBoundingClientRect();

        return rect.top >= bounds.top && rect.bottom <= bounds.bottom;
    });
}

function scrollEntryIntoView(doc, entry) {
    const win = doc.defaultView;

    for (const container of scrollContainersOf(doc, entry.element)) {
        const rect = entry.rects()[0];
        const bounds = container.getBoundingClientRect();

        if (rect.top < bounds.top || rect.bottom > bounds.bottom) {
            container.scrollTop += rect.top - bounds.top + rect.height / 2 - container.clientHeight / 2;
        }
    }

    entry.element.scrollIntoView({ block: 'center', inline: 'nearest' });

    const rect = entry.rects()[0];

    if (rect && (rect.top < 0 || rect.bottom > win.innerHeight)) {
        win.scrollBy(0, rect.top + rect.height / 2 - win.innerHeight / 2);
    }
}

function scrollFieldIntoView(doc, entries) {
    const win = doc.defaultView;
    const occurrences = entries.flatMap(entry => {
        return entry.rects()
            .filter(rect => rect.width && rect.height)
            .map(rect => ({ entry, rect }));
    });

    if (!occurrences.length || occurrences.some(({ entry, rect }) => isInView(doc, entry, rect))) return;

    const distanceFromCenter = ({ rect }) => Math.abs(rect.top - win.innerHeight / 2);
    const nearest = occurrences.sort((a, b) => distanceFromCenter(a) - distanceFromCenter(b))[0];

    scrollEntryIntoView(doc, nearest.entry);
}

function createTimedHighlight(win, fadeDuration, onChange) {
    let path = null;
    let faded = false;
    let hideTimer;
    let clearTimer;

    function clear() {
        win.clearTimeout(hideTimer);
        win.clearTimeout(clearTimer);
    }

    return {
        get path() {
            return path;
        },

        get faded() {
            return faded;
        },

        show(newPath, duration) {
            clear();
            path = newPath;
            faded = false;
            onChange();

            hideTimer = win.setTimeout(() => {
                faded = true;
                onChange();

                clearTimer = win.setTimeout(() => {
                    path = null;
                    faded = false;
                    onChange();
                }, fadeDuration);
            }, duration);
        },

        dispose: clear,
    };
}

function isPlainClick(event) {
    return event.button === 0 && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey;
}

export function createFieldSync(doc, onSelect, onCancel = () => {}) {
    const win = doc.defaultView;
    const overlay = createHighlightOverlay(doc);

    let index = createFieldIndex(doc, null);
    let selecting = false;
    let hovered = null;
    let frame = null;
    let disposed = false;

    function draw() {
        if (disposed) return;

        overlay.draw(hovered ? [hovered] : index.forPath(highlight.path), { faded: highlight.faded && !hovered });
    }

    const highlight = createTimedHighlight(win, overlay.fadeDuration, draw);

    function scheduleDraw() {
        if (frame !== null) return;

        frame = win.requestAnimationFrame(() => {
            frame = null;
            draw();
        });
    }

    function hover(entry) {
        hovered = entry;
        scheduleDraw();
    }

    function reindex() {
        index = createFieldIndex(doc, readFieldManifest(doc));
        hovered = null;
        draw();
    }

    function handlePointerMove(event) {
        if (selecting) hover(index.at(event.clientX, event.clientY, event.target));
    }

    function handleClick(event) {
        if (!selecting || !isPlainClick(event)) return;

        const entry = index.at(event.clientX, event.clientY, event.target);
        if (!entry) return;

        event.preventDefault();
        event.stopImmediatePropagation();

        const [field] = entry.fields;
        highlight.show(pathOf(field), fieldHighlightDuration);
        onSelect(field);
    }

    function handleKeydown(event) {
        if (!selecting || event.key !== 'Escape') return;

        event.preventDefault();
        event.stopImmediatePropagation();
        select(false);
        onCancel();
    }

    function select(enabled) {
        selecting = enabled;
        hovered = null;
        draw();
    }

    const observer = new win.MutationObserver(mutations => {
        if (mutations.some(mutation => !overlay.contains(mutation.target))) reindex();
    });

    observer.observe(doc.documentElement, {
        subtree: true,
        childList: true,
        characterData: true,
        attributes: true,
        attributeFilter: [fieldAttribute],
    });

    const listeners = [
        [doc, 'pointermove', handlePointerMove],
        [doc, 'pointerleave', () => hover(null)],
        [doc, 'click', handleClick, true],
        [doc, 'keydown', handleKeydown, true],
        [win, 'scroll', scheduleDraw, true],
        [win, 'resize', scheduleDraw],
    ];

    listeners.forEach(([target, event, handler, capture]) => target.addEventListener(event, handler, capture));

    reindex();

    return {
        select,

        highlight(path, duration = fieldHighlightDuration) {
            scrollFieldIntoView(doc, index.forPath(path));
            highlight.show(path, duration);
        },

        dispose() {
            disposed = true;
            highlight.dispose();
            observer.disconnect();
            if (frame !== null) win.cancelAnimationFrame(frame);
            listeners.forEach(([target, event, handler, capture]) => target.removeEventListener(event, handler, capture));
            overlay.dispose();
        },
    };
}

export function findPublishField(container, path) {
    const fields = [...(container?.querySelectorAll('[data-preview-field-path]') ?? [])];
    const parts = [...path];

    while (parts.length) {
        const field = fields.find(el => el.dataset.previewFieldPath === parts.join('.'));
        if (field) return field;
        parts.pop();
    }

    return null;
}
