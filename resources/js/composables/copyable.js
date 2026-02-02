import { ref, computed } from 'vue';

export function useCopyable(value, enabled) {
    const copySupported = computed(() => 'clipboard' in navigator && typeof navigator.clipboard.writeText === 'function');
    const copyable = computed(() => enabled.value && copySupported.value);
    const copied = ref(false);

    const copy = () => {
        if (!copyable.value || !value.value) return;

        navigator.clipboard
            .writeText(value.value)
            .then(() => {
                copied.value = true;
                setTimeout(() => (copied.value = false), 1000);
                Statamic.$toast.success(__('Copied to clipboard'));
            })
            .catch(() => {
                Statamic.$toast.error(__('Unable to copy to clipboard'));
            });
    };

    return {
        copyable,
        copied,
        copy,
    };
}
