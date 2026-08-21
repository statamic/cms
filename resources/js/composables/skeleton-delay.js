import { ref, watch } from 'vue';

export default function useSkeletonDelay(isLoading, delay = 400) {
    const shouldShowSkeleton = ref(false);
    let timer = null;

    watch(isLoading, (loading) => {
        clearTimeout(timer);

        if (loading) {
            timer = setTimeout(() => {
                shouldShowSkeleton.value = true;
            }, delay);
            return;
        }

        shouldShowSkeleton.value = false;
    }, { immediate: true });

    return shouldShowSkeleton;
}
