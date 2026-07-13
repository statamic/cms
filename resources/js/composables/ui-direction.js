import { computed, ref } from 'vue';

const direction = ref(document.documentElement.dir || 'ltr');

new MutationObserver(() => {
    direction.value = document.documentElement.dir || 'ltr';
}).observe(document.documentElement, { attributes: true, attributeFilter: ['dir'] });

export function useUiDirection() {
    return {
        uiDirection: computed(() => direction.value),
    };
}
