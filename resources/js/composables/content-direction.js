import { computed, unref } from 'vue';
import { injectContainerContext } from '@/components/ui/Publish/Container.vue';
import { useUiDirection } from './ui-direction';

export function useContentDirection() {
    const container = injectContainerContext();
    const { direction: uiDirection } = useUiDirection();

    return {
        direction: computed(() => (container ? unref(container.direction) : uiDirection.value)),
    };
}
