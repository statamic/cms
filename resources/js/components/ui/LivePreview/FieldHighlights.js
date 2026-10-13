const outlineColor = '96 165 250';
const outlinePadding = 8;
const fadeDuration = 180;

export function createHighlightOverlay(doc) {
    const win = doc.defaultView;
    const duration = win.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : fadeDuration;

    const overlay = doc.createElement('div');
    overlay.setAttribute('aria-hidden', 'true');
    overlay.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:2147483647;';
    overlay.style.transition = `opacity ${duration}ms ease-out`;
    doc.documentElement.append(overlay);

    function createOutline(rect) {
        const outline = doc.createElement('div');

        Object.assign(outline.style, {
            position: 'absolute',
            left: `${rect.left - outlinePadding}px`,
            top: `${rect.top - outlinePadding}px`,
            width: `${rect.width + outlinePadding * 2}px`,
            height: `${rect.height + outlinePadding * 2}px`,
            border: `2px solid rgb(${outlineColor})`,
            background: `rgb(${outlineColor} / 5%)`,
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
                const rect = entry.boundingRect();
                if (!isOnScreen(rect)) continue;

                const key = [rect.left, rect.top, rect.width, rect.height].join(',');
                if (drawn.has(key)) continue;
                drawn.add(key);

                overlay.append(createOutline(rect));
            }
        },

        dispose() {
            overlay.remove();
        },
    };
}
