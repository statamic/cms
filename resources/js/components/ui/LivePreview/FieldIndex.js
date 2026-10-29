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

export function fieldPath(field) {
    return field.path.join('.');
}

function createFieldEntry(fields, element, range, depth) {
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

function isSelectable(entry) {
    return entry.fields.length === 1;
}

export function createFieldIndex(doc, manifest) {
    const byElement = new WeakMap();
    const byPath = new Map();

    function ownedFieldsFor(marker) {
        return (manifest.markers[marker] ?? [])
            .map(id => manifest.fields[id])
            .filter(field => {
                return Array.isArray(field?.path)
                    && field.reference === manifest.owner.reference
                    && field.locale === manifest.owner.locale;
            });
    }

    function register(marker, element, range = null, depth = 0) {
        const fields = ownedFieldsFor(marker);
        if (!fields.length || !element) return;

        const entry = createFieldEntry(fields, element, range, depth);

        byElement.set(element, [...(byElement.get(element) ?? []), entry]);

        for (const field of fields) {
            const path = fieldPath(field);
            byPath.set(path, [...(byPath.get(path) ?? []), entry]);
        }
    }

    function rangeBetween(start, end) {
        const range = doc.createRange();
        range.setStartAfter(start);
        range.setEndBefore(end);

        return range;
    }

    function indexDocument() {
        const win = doc.defaultView;
        const walker = doc.createTreeWalker(doc.body, win.NodeFilter.SHOW_ELEMENT | win.NodeFilter.SHOW_COMMENT);
        const openMarkers = new Map();
        let depth = 0;

        const indexElement = element => {
            if (element.hasAttribute(fieldAttribute)) register(element.getAttribute(fieldAttribute), element);
        };

        const indexComment = comment => {
            const match = comment.data.match(markerComment);
            if (!match) return;

            const [, closing, marker] = match;

            if (!closing) {
                openMarkers.set(marker, { node: comment, depth: ++depth });
                return;
            }

            const opened = openMarkers.get(marker);
            openMarkers.delete(marker);
            depth = Math.max(0, depth - 1);

            if (opened && opened.node.parentNode === comment.parentNode) {
                register(marker, comment.parentElement, rangeBetween(opened.node, comment), opened.depth);
            }
        };

        while (walker.nextNode()) {
            const node = walker.currentNode;

            node.nodeType === win.Node.ELEMENT_NODE ? indexElement(node) : indexComment(node);
        }
    }

    if (manifest && doc.body) indexDocument();

    return {
        entriesFor(path) {
            return byPath.get(path) ?? [];
        },

        entryAt(x, y, target) {
            for (let element = target; element; element = element.parentElement) {
                const hits = (byElement.get(element) ?? []).filter(entry => isSelectable(entry) && entry.contains(x, y));

                if (hits.length) return hits.sort((a, b) => b.depth - a.depth)[0];
            }

            return null;
        },
    };
}
