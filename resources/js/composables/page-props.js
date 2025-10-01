import { usePage } from '@inertiajs/vue3';

export default function useStatamicPageProps() {
    return usePage().props._statamic;
}
