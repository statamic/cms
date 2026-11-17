import { createHighlightOverlay, createTimedHighlight } from './FieldHighlights.js';
import { createFieldIndex, fieldPath, readFieldManifest } from './FieldIndex.js';
import { scrollFieldIntoView } from './FieldScrolling.js';

export const fieldHighlightDuration = 2000;

function isPlainClick(event) {
    return event.button === 0 && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey;
}

export function createFieldSync(doc, onSelect, onCancel = () => {}) {
    const win = doc.defaultView;
    const overlay = createHighlightOverlay(doc);
    const activeHighlight = createTimedHighlight(win, overlay.fadeDuration, draw);

    let index = createFieldIndex(doc, null);
    let selecting = false;
    let hovered = null;
    let frame = null;
    let disposed = false;

    function draw() {
        if (disposed) return;

        const entries = hovered ? [hovered] : index.entriesFor(activeHighlight.path);

        overlay.draw(entries, { faded: activeHighlight.faded && !hovered });
    }

    function scheduleDraw() {
        if (frame !== null) return;

        frame = win.requestAnimationFrame(() => {
            frame = null;
            draw();
        });
    }

    function reindex() {
        index = createFieldIndex(doc, readFieldManifest(doc));
        hovered = null;
        draw();
    }

    function hover(entry) {
        hovered = entry;
        scheduleDraw();
    }

    function select(enabled) {
        selecting = enabled;
        hovered = null;
        draw();
    }

    function handlePointerMove(event) {
        if (selecting) hover(index.entryAt(event.clientX, event.clientY, event.target));
    }

    function handleClick(event) {
        if (!selecting || !isPlainClick(event)) return;

        const entry = index.entryAt(event.clientX, event.clientY, event.target);
        if (!entry) return;

        event.preventDefault();
        event.stopImmediatePropagation();

        const [field] = entry.fields;
        activeHighlight.show(fieldPath(field), fieldHighlightDuration);
        onSelect(field);
    }

    function handleKeydown(event) {
        if (!selecting || event.key !== 'Escape') return;

        event.preventDefault();
        event.stopImmediatePropagation();
        select(false);
        onCancel();
    }

    const observer = new win.MutationObserver(mutations => {
        if (mutations.some(mutation => !overlay.contains(mutation.target))) reindex();
    });

    observer.observe(doc.documentElement, {
        subtree: true,
        childList: true,
        characterData: true,
        attributes: true,
        attributeFilter: ['data-statamic-field'],
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
            scrollFieldIntoView(doc, index.entriesFor(path));
            activeHighlight.show(path, duration);
        },

        dispose() {
            disposed = true;
            activeHighlight.dispose();
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
