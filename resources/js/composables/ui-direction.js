import { computed, ref } from 'vue';

const dir = ref(typeof document !== 'undefined' ? document.documentElement.dir || 'ltr' : 'ltr');

if (typeof document !== 'undefined') {
    new MutationObserver(() => {
        dir.value = document.documentElement.dir || 'ltr';
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['dir'] });
}

export function useUiDirection() {
    return {
        direction: computed(() => dir.value),
    };
}
