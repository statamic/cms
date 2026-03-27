import { watch, nextTick, onUnmounted } from 'vue';

export default function useResizable() {
    const cleanupFns = [];

    function makeResizable(panelRef, activeRef, { edge = 'right', minWidth = 200, maxWidth = 800, defaultWidth = null } = {}) {
        let cleanup = null;

        watch(activeRef, (active) => {
            if (active) {
                nextTick(() => {
                    const panel = panelRef.value;
                    if (!panel) return;

                    const isRtl = document.documentElement.dir === 'rtl';
                    const resolvedEdge = isRtl ? (edge === 'right' ? 'left' : 'right') : edge;

                    if (defaultWidth) {
                        panel.style.width = `${defaultWidth}px`;
                    }

                    const handle = document.createElement('div');
                    handle.style.position = 'absolute';
                    handle.style.top = '0';
                    handle.style.bottom = '0';
                    handle.style.width = '5px';
                    handle.style.cursor = 'col-resize';
                    handle.style.zIndex = '10';
                    handle.style[resolvedEdge] = '-2px';

                    panel.style.position = 'relative';
                    panel.appendChild(handle);

                    const onPointerDown = (e) => {
                        e.preventDefault();
                        const startX = e.clientX;
                        const startWidth = panel.offsetWidth;

                        document.body.style.cursor = 'col-resize';
                        document.body.style.userSelect = 'none';

                        const onMove = (e) => {
                            const diff = resolvedEdge === 'right' ? e.clientX - startX : startX - e.clientX;
                            const newWidth = Math.min(maxWidth, Math.max(minWidth, startWidth + diff));
                            panel.style.width = `${newWidth}px`;
                        };

                        const onUp = () => {
                            document.body.style.cursor = '';
                            document.body.style.userSelect = '';
                            document.removeEventListener('pointermove', onMove);
                            document.removeEventListener('pointerup', onUp);
                        };

                        document.addEventListener('pointermove', onMove);
                        document.addEventListener('pointerup', onUp);
                    };

                    handle.addEventListener('pointerdown', onPointerDown);

                    cleanup = () => {
                        handle.removeEventListener('pointerdown', onPointerDown);
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
