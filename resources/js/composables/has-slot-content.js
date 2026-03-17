import { useSlots, computed, Comment, Fragment } from 'vue';

export function hasSlotContent(slotName, slotProps = {}) {
    const slots = useSlots();

    return computed(() => {
        if (!slots[slotName]) return false;

        // Call the slot with props to get the actual vnodes
        const slotContent = slots[slotName](slotProps.value || slotProps);

        const hasRealContent = (vnodes) => {
            if (!vnodes || vnodes.length === 0) return false;

            return vnodes.some(vnode => {
                // Skip comments
                if (vnode.type === Comment) return false;

                // Skip empty text nodes
                if (typeof vnode.children === 'string' && !vnode.children.trim()) return false;

                // Handle fragments (like from v-for)
                if (vnode.type === Fragment) {
                    return hasRealContent(vnode.children);
                }

                // If it has array children, recursively check them
                if (Array.isArray(vnode.children)) {
                    return hasRealContent(vnode.children);
                }

                // Otherwise it's real content
                return true;
            });
        };

        return hasRealContent(slotContent);
    });
}
