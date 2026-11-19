import { useSlots, computed, unref } from 'vue';

export function hasComponent(componentName, slotProps = {}) {
    const slots = useSlots();

    return computed(() => {
        const defaultSlot = slots.default?.(unref(slotProps));
        if (!defaultSlot) return false;

        return defaultSlot.some((vnode) => {
            const type = vnode.type;
            return type?.name === componentName || type?.__name === componentName;
        });
    });
}
