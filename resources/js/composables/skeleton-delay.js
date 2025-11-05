import { ref, watch } from 'vue';

export default function useSkeletonDelay(isLoading, delay = 300) {
    const shouldShowSkeleton = ref(false);

    const timer = setTimeout(() => shouldShowSkeleton.value = isLoading.value, delay);

    watch(isLoading, (loading) => {
        if (!loading) {
            clearTimeout(timer);
            shouldShowSkeleton.value = false;
        }
    });

    return shouldShowSkeleton;
}
