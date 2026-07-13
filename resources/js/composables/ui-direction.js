import { computed, ref } from 'vue';

const dir = ref(document.documentElement.dir || 'ltr');

new MutationObserver(() => {
    dir.value = document.documentElement.dir || 'ltr';
}).observe(document.documentElement, { attributes: true, attributeFilter: ['dir'] });

export function useUiDirection() {
    return {
        direction: computed(() => dir.value),
    };
}
