import throttle from '@/util/throttle.js';

export function createTabsOverflowTracker({
    getWrapper,
    getInner,
    getItems,
    onUpdate,
    wait = 100,
}) {
    let observer = null;
    const throttledCheck = throttle(() => checkOverflow(), wait);

    function checkOverflow() {
        const wrapper = getWrapper();
        const inner = getInner();
        const items = getItems();

        if (!wrapper || !inner || !items.length) {
            onUpdate({ hasOverflow: false, overflowedItems: [] });
            return;
        }

        const hasOverflow = inner.scrollWidth > wrapper.clientWidth;

        if (!hasOverflow) {
            onUpdate({ hasOverflow: false, overflowedItems: [] });
            return;
        }

        const wrapperRect = wrapper.getBoundingClientRect();
        const tabNodes = inner.querySelectorAll('[role="tab"]');
        const overflowedItems = items.filter((item, index) => {
            const node = tabNodes[index];

            if (!node) {
                return false;
            }

            const rect = node.getBoundingClientRect();

            return rect.right > wrapperRect.right || rect.left < wrapperRect.left;
        });

        onUpdate({ hasOverflow: true, overflowedItems });
    }

    function observe() {
        observer?.disconnect();
        observer = null;

        const wrapper = getWrapper();

        if (!wrapper) {
            return;
        }

        observer = new ResizeObserver(throttledCheck);
        observer.observe(wrapper);
    }

    function disconnect() {
        observer?.disconnect();
        observer = null;
        throttledCheck.cancel();
    }

    return {
        checkOverflow,
        observe,
        disconnect,
    };
}
