import { watch, nextTick, onUnmounted } from 'vue';

function getStoredWidth(key) {
    if (!key) return null;

    const stored = localStorage.getItem(key);
    if (stored === null) return null;

    const width = parseInt(stored, 10);

    return Number.isFinite(width) ? width : null;
}

function setStoredWidth(key, width) {
    if (!key) return;

    localStorage.setItem(key, String(width));
}

function clearStoredWidth(key) {
    if (!key) return;

    localStorage.removeItem(key);
}

export default function useResizable() {
    const cleanupFns = [];

    function makeResizable(
        panelRef,
        activeRef,
        { edge = 'right', minWidth = 200, maxWidth = 800, defaultWidth = null, storageKey = null } = {},
    ) {
        let cleanup = null;

        watch(activeRef, (active) => {
            if (active) {
                nextTick(() => {
                    const panel = panelRef.value;
                    if (!panel) return;

                    const isRtl = document.documentElement.dir === 'rtl';
                    const resolvedEdge = isRtl ? (edge === 'right' ? 'left' : 'right') : edge;
                    const resolveDefaultWidth = () =>
                        typeof defaultWidth === 'function' ? defaultWidth() : defaultWidth;
                    const clampWidth = (width) => Math.min(maxWidth, Math.max(minWidth, width));

                    const storedWidth = getStoredWidth(storageKey);
                    const initialWidth = storedWidth ?? resolveDefaultWidth();

                    if (initialWidth !== null && initialWidth !== undefined) {
                        panel.style.width = `${clampWidth(initialWidth)}px`;
                    }

                    const handle = document.createElement('div');
                    handle.dataset.resizeHandle = '';
                    handle.style.position = 'absolute';
                    handle.style.top = '0';
                    handle.style.bottom = '0';
                    handle.style.width = '7px';
                    handle.style.cursor = 'col-resize';
                    handle.style.zIndex = 'var(--z-index-draggable)';
                    handle.style[resolvedEdge] = '0px';

                    panel.appendChild(handle);

                    const resetToDefaultWidth = () => {
                        clearStoredWidth(storageKey);

                        const currentDefaultWidth = resolveDefaultWidth();
                        if (currentDefaultWidth !== null && currentDefaultWidth !== undefined) {
                            panel.style.width = `${clampWidth(currentDefaultWidth)}px`;
                            return;
                        }

                        panel.style.width = '';
                    };

                    const onPointerDown = (e) => {
                        if (e.detail === 2) {
                            resetToDefaultWidth();
                            return;
                        }

                        e.preventDefault();
                        const startX = e.clientX;
                        const startWidth = panel.offsetWidth;

                        document.body.style.cursor = 'col-resize';
                        document.body.style.userSelect = 'none';

                        const onMove = (e) => {
                            const diff = resolvedEdge === 'right' ? e.clientX - startX : startX - e.clientX;
                            const newWidth = clampWidth(startWidth + diff);
                            panel.style.width = `${newWidth}px`;
                        };

                        const onUp = () => {
                            document.body.style.cursor = '';
                            document.body.style.userSelect = '';
                            document.removeEventListener('pointermove', onMove);
                            document.removeEventListener('pointerup', onUp);
                            setStoredWidth(storageKey, panel.offsetWidth);
                        };

                        document.addEventListener('pointermove', onMove);
                        document.addEventListener('pointerup', onUp);
                    };

                    const onDoubleClick = () => {
                        resetToDefaultWidth();
                    };

                    handle.addEventListener('pointerdown', onPointerDown);
                    handle.addEventListener('dblclick', onDoubleClick);

                    cleanup = () => {
                        handle.removeEventListener('pointerdown', onPointerDown);
                        handle.removeEventListener('dblclick', onDoubleClick);
                        handle.remove();
                    };
                });
            } else {
                cleanup?.();
                cleanup = null;
            }
        });

        cleanupFns.push(() => {
            cleanup?.();
            cleanup = null;
        });
    }

    onUnmounted(() => {
        cleanupFns.forEach((fn) => fn());
    });

    return { makeResizable };
}
