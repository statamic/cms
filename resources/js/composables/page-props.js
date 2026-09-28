import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export default function useStatamicPageProps() {
    const page = usePage();

    return new Proxy({}, {
        get(target, prop) {
            return page.props._statamic?.[prop];
        }
    });
}

export function useReactiveStatamicPageProps() {
    const page = usePage();

    return new Proxy({}, {
        get(target, prop) {
            if (!target[prop]) {
                target[prop] = computed(() => page.props._statamic?.[prop]);
            }
            return target[prop];
        }
    });
}
