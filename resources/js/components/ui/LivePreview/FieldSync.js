export const fieldHighlightDuration = 2000;

export function createFieldSync(doc, onSelect) {
    const win = doc.defaultView;
    let entriesByElement = new WeakMap();
    let entriesByPath = new Map();
    let selectedPath = null;
    let selecting = false;
    let hoveredEntry = null;
    let frame = null;
    let disposed = false;
    let hideTimer;
    let clearTimer;
    let fading = false;

    const fadeDuration = win.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 180;
    const canvas = doc.createElement('canvas');
    canvas.width = 1;
    canvas.height = 1;
    const colors = canvas.getContext('2d', { willReadFrequently: true });

    const overlay = doc.createElement('div');
    overlay.setAttribute('aria-hidden', 'true');
    overlay.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:2147483647;';
    overlay.style.transition = `opacity ${fadeDuration}ms ease-out`;
    doc.documentElement.append(overlay);

    function indexFields() {
        entriesByElement = new WeakMap();
        entriesByPath = new Map();
        hoveredEntry = null;

        const manifest = readManifest();
        if (!doc.body || !manifest?.owner || !manifest.fields || !manifest.markers) {
            drawHighlights();
            return;
        }

        const walker = doc.createTreeWalker(doc.body, win.NodeFilter.SHOW_ELEMENT | win.NodeFilter.SHOW_COMMENT);
        const starts = new Map();
        let depth = 0;

        while (walker.nextNode()) {
            const node = walker.currentNode;
            if (node.nodeType === win.Node.ELEMENT_NODE) {
                if (node.hasAttribute('data-statamic-field')) {
                    addEntry(manifest, node.getAttribute('data-statamic-field'), node);
                }
                continue;
            }

            const match = node.data.match(/^(\/?)statamic:field:(\d+)$/);
            if (!match) continue;

            if (!match[1]) {
                depth++;
                starts.set(match[2], { node, depth });
            } else {
                const start = starts.get(match[2]);
                starts.delete(match[2]);
                depth = Math.max(0, depth - 1);

                // Browser repair or application morphing can invalidate a boundary pair.
                if (!start || start.node.parentNode !== node.parentNode) continue;

                const range = doc.createRange();
                range.setStartAfter(start.node);
                range.setEndBefore(node);
                addEntry(manifest, match[2], node.parentElement, range, start.depth);
            }
        }

        drawHighlights();
    }

    function readManifest() {
        try {
            return JSON.parse(doc.getElementById('statamic-preview-fields')?.textContent ?? 'null');
        } catch {
            return null;
        }
    }

    function addEntry(manifest, marker, element, range = null, depth = 0) {
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
        if (!entriesByElement.has(element)) entriesByElement.set(element, []);
        entriesByElement.get(element).push(entry);

        for (const field of fields) {
            const path = field.path.join('.');
            if (!entriesByPath.has(path)) entriesByPath.set(path, []);
            entriesByPath.get(path).push(entry);
        }
    }

    function getRects(entry) {
        if (entry.range) {
            return Array.from(entry.range.getClientRects());
        }

        return [entry.element.getBoundingClientRect()];
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
        const matches = (entriesByPath.get(path) ?? []).flatMap(entry => {
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
            for (const entry of entriesByElement.get(el) ?? []) {
                if (entry.fields.length !== 1) continue;

                const containsPointer = getRects(entry).some(rect => {
                    return event.clientX >= rect.left && event.clientX <= rect.right
                        && event.clientY >= rect.top && event.clientY <= rect.bottom;
                });

                if (containsPointer) {
                    candidates.push(entry);
                }
            }
            if (candidates.length) break;
        }

        // Prefer the innermost field when comment ranges share a parent.
        return candidates.sort((a, b) => b.depth - a.depth)[0] ?? null;
    }

    function highlightColor(element) {
        const ancestors = [];
        for (let current = element; current; current = current.parentElement) {
            ancestors.unshift(current);
        }

        colors.fillStyle = win.getComputedStyle(doc.documentElement).colorScheme === 'dark' ? '#121212' : '#ffffff';
        colors.fillRect(0, 0, 1, 1);
        for (const ancestor of ancestors) {
            colors.fillStyle = win.getComputedStyle(ancestor).backgroundColor;
            colors.fillRect(0, 0, 1, 1);
        }

        const [red, green, blue] = colors.getImageData(0, 0, 1, 1).data;
        const brightness = (red * 299 + green * 587 + blue * 114) / 1000;

        return brightness < 128 ? '147 197 253' : '59 130 246';
    }

    function showHighlight(path, duration = fieldHighlightDuration) {
        win.clearTimeout(hideTimer);
        win.clearTimeout(clearTimer);
        selectedPath = path;
        hoveredEntry = null;
        fading = false;
        drawHighlights();

        hideTimer = win.setTimeout(() => {
            fading = true;
            drawHighlights();
            clearTimer = win.setTimeout(() => {
                selectedPath = null;
                fading = false;
                drawHighlights();
            }, fadeDuration);
        }, duration);
    }

    function drawHighlights() {
        if (disposed) return;

        overlay.replaceChildren();
        overlay.style.opacity = fading && !hoveredEntry ? '0' : '1';
        let entries;
        if (hoveredEntry) {
            entries = [hoveredEntry];
        } else {
            entries = entriesByPath.get(selectedPath) ?? [];
        }
        const boxes = new Set();

        for (const entry of entries) {
            const rect = entry.range?.getBoundingClientRect() ?? entry.element.getBoundingClientRect();

            if (!rect.width || !rect.height || rect.bottom < 0 || rect.top > win.innerHeight) continue;
            const key = [rect.left, rect.top, rect.width, rect.height].join(',');
            if (boxes.has(key)) continue;
            boxes.add(key);

            const color = highlightColor(entry.element);
            const box = doc.createElement('div');
            Object.assign(box.style, {
                position: 'absolute',
                left: `${rect.left - 3}px`,
                top: `${rect.top - 3}px`,
                width: `${rect.width + 6}px`,
                height: `${rect.height + 6}px`,
                border: `1px solid rgb(${color} / 85%)`,
                background: `rgb(${color} / 5%)`,
                boxShadow: `0 0 0 2px rgb(${color} / 10%)`,
                borderRadius: '5px',
                boxSizing: 'border-box',
            });
            overlay.append(box);
        }
    }

    function scheduleDraw() {
        if (frame !== null) return;

        frame = win.requestAnimationFrame(() => {
            frame = null;
            drawHighlights();
        });
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

    const observer = new win.MutationObserver(mutations => {
        // Ignore our own drawing when watching for body replacements.
        if (mutations.some(mutation => !overlay.contains(mutation.target))) indexFields();
    });
    observer.observe(doc.documentElement, {
        subtree: true,
        childList: true,
        characterData: true,
        attributes: true,
        attributeFilter: ['data-statamic-field'],
    });
    doc.addEventListener('pointermove', handlePointerMove);
    doc.addEventListener('pointerleave', handlePointerLeave);
    doc.addEventListener('click', handleClick, true);
    win.addEventListener('scroll', scheduleDraw, true);
    win.addEventListener('resize', scheduleDraw);
    indexFields();

    return {
        select(enabled) {
            selecting = enabled;
            hoveredEntry = null;
            drawHighlights();
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
            win.removeEventListener('scroll', scheduleDraw, true);
            win.removeEventListener('resize', scheduleDraw);
            overlay.remove();
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
}
