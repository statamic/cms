const outlineOnDark = '147 197 253';
const outlineOnLight = '59 130 246';
const outlinePadding = 3;
const fadeDuration = 180;

export function createHighlightOverlay(doc) {
    const win = doc.defaultView;
    const duration = win.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : fadeDuration;

    const canvas = doc.createElement('canvas');
    canvas.width = 1;
    canvas.height = 1;
    const composite = canvas.getContext('2d', { willReadFrequently: true });

    const overlay = doc.createElement('div');
    overlay.setAttribute('aria-hidden', 'true');
    overlay.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:2147483647;';
    overlay.style.transition = `opacity ${duration}ms ease-out`;
    doc.documentElement.append(overlay);

    // Backgrounds may be transparent, so flatten the element onto its ancestors to see what is really behind it.
    function backgroundBrightness(element) {
        const ancestors = [];
        for (let current = element; current; current = current.parentElement) {
            ancestors.unshift(current);
        }

        composite.fillStyle = win.getComputedStyle(doc.documentElement).colorScheme === 'dark' ? '#121212' : '#ffffff';
        composite.fillRect(0, 0, 1, 1);

        for (const ancestor of ancestors) {
            composite.fillStyle = win.getComputedStyle(ancestor).backgroundColor;
            composite.fillRect(0, 0, 1, 1);
        }

        const [red, green, blue] = composite.getImageData(0, 0, 1, 1).data;

        return (red * 299 + green * 587 + blue * 114) / 1000;
    }

    function createOutline(rect, color) {
        const outline = doc.createElement('div');

        Object.assign(outline.style, {
            position: 'absolute',
            left: `${rect.left - outlinePadding}px`,
            top: `${rect.top - outlinePadding}px`,
            width: `${rect.width + outlinePadding * 2}px`,
            height: `${rect.height + outlinePadding * 2}px`,
            border: `1px solid rgb(${color} / 85%)`,
            background: `rgb(${color} / 5%)`,
            boxShadow: `0 0 0 2px rgb(${color} / 10%)`,
            borderRadius: '5px',
            boxSizing: 'border-box',
        });

        return outline;
    }

    function isOnScreen(rect) {
        return rect.width && rect.height && rect.bottom >= 0 && rect.top <= win.innerHeight;
    }

    return {
        fadeDuration: duration,

        contains(node) {
            return overlay.contains(node);
        },

        draw(entries, { faded = false } = {}) {
            overlay.replaceChildren();
            overlay.style.opacity = faded ? '0' : '1';

            const drawn = new Set();

            for (const entry of entries) {
                const rect = entry.range?.getBoundingClientRect() ?? entry.element.getBoundingClientRect();
                if (!isOnScreen(rect)) continue;

                const key = [rect.left, rect.top, rect.width, rect.height].join(',');
                if (drawn.has(key)) continue;
                drawn.add(key);

                overlay.append(createOutline(rect, backgroundBrightness(entry.element) < 128 ? outlineOnDark : outlineOnLight));
            }
        },

        dispose() {
            overlay.remove();
        },
    };
}
