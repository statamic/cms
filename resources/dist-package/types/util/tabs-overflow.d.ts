export function createTabsOverflowTracker({ getWrapper, getInner, getItems, onUpdate, wait, }: {
    getWrapper: any;
    getInner: any;
    getItems: any;
    onUpdate: any;
    wait?: number | undefined;
}): {
    checkOverflow: () => void;
    observe: () => void;
    disconnect: () => void;
};
