import { createHighlightOverlay } from './FieldHighlights.js';

export const fieldHighlightDuration = 2000;

export const fieldManifestId = 'statamic-preview-fields';
const fieldAttribute = 'data-statamic-field';
const markerComment = /^(\/?)statamic:field:(\d+)$/;

function emptyIndex() {
    return { byElement: new WeakMap(), byPath: new Map() };
}

function getRects(entry) {
    return entry.range ? Array.from(entry.range.getClientRects()) : [entry.element.getBoundingClientRect()];
}

function addEntry(index, manifest, marker, { element, range = null, depth = 0 }) {
    const fields = (manifest.markers[marker] ?? [])
        .map(id => manifest.fields[id])
        .filter(field => {
            return field
                && Array.isArray(field.path)
                && field.reference === manifest.owner.reference
                && field.locale === manifest.owner.locale;
        });

    if (!fields.length || !element) return;

    const entry = { fields, element, range, depth };

    if (!index.byElement.has(element)) index.byElement.set(element, []);
    index.byElement.get(element).push(entry);

    for (const field of fields) {
        const path = field.path.join('.');
        if (!index.byPath.has(path)) index.byPath.set(path, []);
        index.byPath.get(path).push(entry);
    }
}

export function createFieldSync(doc, onSelect, onCancel = () => {}) {
    const win = doc.defaultView;
    const highlights = createHighlightOverlay(doc);

    let index = emptyIndex();
    let selectedPath = null;
    let selecting = false;
    let hoveredEntry = null;
    let fading = false;
    let frame = null;
    let disposed = false;
    let hideTimer;
    let clearTimer;

    function readManifest() {
        try {
            return JSON.parse(doc.getElementById(fieldManifestId)?.textContent ?? 'null');
        } catch {
            return null;
        }
    }

    function buildIndex() {
        const index = emptyIndex();
        const manifest = readManifest();

        if (!doc.body || !manifest?.owner || !manifest.fields || !manifest.markers) return index;

        const walker = doc.createTreeWalker(doc.body, win.NodeFilter.SHOW_ELEMENT | win.NodeFilter.SHOW_COMMENT);
        const openMarkers = new Map();
        let depth = 0;

        while (walker.nextNode()) {
            const node = walker.currentNode;

            if (node.nodeType === win.Node.ELEMENT_NODE) {
                if (node.hasAttribute(fieldAttribute)) {
                    addEntry(index, manifest, node.getAttribute(fieldAttribute), { element: node });
                }
                continue;
            }

            const match = node.data.match(markerComment);
            if (!match) continue;

            const [, isClosing, marker] = match;

            if (!isClosing) {
                depth++;
                openMarkers.set(marker, { node, depth });
                continue;
            }

            const start = openMarkers.get(marker);
            openMarkers.delete(marker);
            depth = Math.max(0, depth - 1);

            // Browser repair or application morphing can invalidate a boundary pair.
            if (!start || start.node.parentNode !== node.parentNode) continue;

            const range = doc.createRange();
            range.setStartAfter(start.node);
            range.setEndBefore(node);
            addEntry(index, manifest, marker, { element: node.parentElement, range, depth: start.depth });
        }

        return index;
    }

    function reindex() {
        index = buildIndex();
        hoveredEntry = null;
        draw();
    }

    function entriesFor(path) {
        return index.byPath.get(path) ?? [];
    }

    function getScrollContainers(entry) {
        const containers = [];

        for (let el = entry.element; el && el !== doc.body; el = el.parentElement) {
            const overflow = win.getComputedStyle(el).overflowY;
            if (el.scrollHeight > el.clientHeight && /auto|scroll|hidden/.test(overflow)) {
                containers.push(el);
            }
        }

        return containers;
    }

    function isVisible({ entry, rect }) {
        return rect.top >= 0
            && rect.bottom <= win.innerHeight
            && rect.left >= 0
            && rect.right <= win.innerWidth
            && getScrollContainers(entry).every(el => {
                const bounds = el.getBoundingClientRect();
                return rect.top >= bounds.top && rect.bottom <= bounds.bottom;
            });
    }

    function scrollToField(path) {
        const matches = entriesFor(path).flatMap(entry => {
            return getRects(entry)
                .filter(rect => rect.width && rect.height)
                .map(rect => ({ entry, rect }));
        });

        // Repeated fields may already have an occurrence in view.
        if (!matches.length || matches.some(isVisible)) return;

        const distanceFromCenter = match => Math.abs(match.rect.top - win.innerHeight / 2);
        const nearest = matches.sort((a, b) => distanceFromCenter(a) - distanceFromCenter(b))[0];

        scrollEntryIntoView(nearest.entry);
    }

    function scrollEntryIntoView(entry) {
        for (const container of getScrollContainers(entry)) {
            const rect = getRects(entry)[0];
            const bounds = container.getBoundingClientRect();

            if (rect.top < bounds.top || rect.bottom > bounds.bottom) {
                container.scrollTop += rect.top - bounds.top + rect.height / 2 - container.clientHeight / 2;
            }
        }

        entry.element.scrollIntoView({ block: 'center', inline: 'nearest' });

        // A text range can be deep inside a tall ancestor; center the actual output.
        const rect = getRects(entry)[0];
        if (rect && (rect.top < 0 || rect.bottom > win.innerHeight)) {
            win.scrollBy(0, rect.top + rect.height / 2 - win.innerHeight / 2);
        }
    }

    function findEntryAtPointer(event) {
        const candidates = [];

        for (let el = event.target; el; el = el.parentElement) {
            for (const entry of index.byElement.get(el) ?? []) {
                if (entry.fields.length !== 1) continue;

                const containsPointer = getRects(entry).some(rect => {
                    return event.clientX >= rect.left && event.clientX <= rect.right
                        && event.clientY >= rect.top && event.clientY <= rect.bottom;
                });

                if (containsPointer) candidates.push(entry);
            }

            if (candidates.length) break;
        }

        // Prefer the innermost field when comment ranges share a parent.
        return candidates.sort((a, b) => b.depth - a.depth)[0] ?? null;
    }

    function draw() {
        if (disposed) return;

        highlights.draw(hoveredEntry ? [hoveredEntry] : entriesFor(selectedPath), { faded: fading && !hoveredEntry });
    }

    function scheduleDraw() {
        if (frame !== null) return;

        frame = win.requestAnimationFrame(() => {
            frame = null;
            draw();
        });
    }

    function showHighlight(path, duration = fieldHighlightDuration) {
        win.clearTimeout(hideTimer);
        win.clearTimeout(clearTimer);
        selectedPath = path;
        hoveredEntry = null;
        fading = false;
        draw();

        hideTimer = win.setTimeout(() => {
            fading = true;
            draw();

            clearTimer = win.setTimeout(() => {
                selectedPath = null;
                fading = false;
                draw();
            }, highlights.fadeDuration);
        }, duration);
    }

    function handlePointerMove(event) {
        if (!selecting) return;

        hoveredEntry = findEntryAtPointer(event);
        scheduleDraw();
    }

    function handlePointerLeave() {
        hoveredEntry = null;
        scheduleDraw();
    }

    function handleClick(event) {
        if (!selecting || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        const entry = findEntryAtPointer(event);
        if (!entry) return;

        event.preventDefault();
        event.stopImmediatePropagation();
        showHighlight(entry.fields[0].path.join('.'));
        onSelect(entry.fields[0]);
    }

    function handleKeydown(event) {
        if (!selecting || event.key !== 'Escape') return;

        event.preventDefault();
        event.stopImmediatePropagation();
        selecting = false;
        hoveredEntry = null;
        draw();
        onCancel();
    }

    const observer = new win.MutationObserver(mutations => {
        // Ignore our own drawing when watching for body replacements.
        if (mutations.some(mutation => !highlights.contains(mutation.target))) reindex();
    });

    observer.observe(doc.documentElement, {
        subtree: true,
        childList: true,
        characterData: true,
        attributes: true,
        attributeFilter: [fieldAttribute],
    });

    doc.addEventListener('pointermove', handlePointerMove);
    doc.addEventListener('pointerleave', handlePointerLeave);
    doc.addEventListener('click', handleClick, true);
    doc.addEventListener('keydown', handleKeydown, true);
    win.addEventListener('scroll', scheduleDraw, true);
    win.addEventListener('resize', scheduleDraw);

    reindex();

    return {
        select(enabled) {
            selecting = enabled;
            hoveredEntry = null;
            draw();
        },
        highlight(path, duration = fieldHighlightDuration) {
            scrollToField(path);
            showHighlight(path, duration);
        },
        dispose() {
            disposed = true;
            win.clearTimeout(hideTimer);
            win.clearTimeout(clearTimer);
            observer.disconnect();
            if (frame !== null) win.cancelAnimationFrame(frame);
            doc.removeEventListener('pointermove', handlePointerMove);
            doc.removeEventListener('pointerleave', handlePointerLeave);
            doc.removeEventListener('click', handleClick, true);
            doc.removeEventListener('keydown', handleKeydown, true);
            win.removeEventListener('scroll', scheduleDraw, true);
            win.removeEventListener('resize', scheduleDraw);
            highlights.dispose();
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
