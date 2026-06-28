import { computed } from 'vue';

export function useUiDirection() {
    return {
        uiDirection: computed(() => document.documentElement.dir || 'ltr'),
    };
}
