import { useSlots, computed } from 'vue';

export function hasComponent(componentName) {
    const slots = useSlots();

    return computed(() => {
        const defaultSlot = slots.default?.();
        if (!defaultSlot) return false;

        return defaultSlot.some((vnode) => {
            const type = vnode.type;
            return type?.name === componentName || type?.__name === componentName;
        });
    });
}
