import { ref, computed } from 'vue';

export default function useCopy() {
    const copySupported = computed(() => 'clipboard' in navigator && typeof navigator.clipboard.writeText === 'function');
    const copiedValue = ref(null);
    const copied = computed(() => copiedValue.value !== null);
    const isCopied = (value) => copiedValue.value === value;
    let timeout;

    const copy = (value) => {
        if (!value) return Promise.resolve();

        return navigator.clipboard
            .writeText(value)
            .then(() => {
                copiedValue.value = value;
                clearTimeout(timeout);
                timeout = setTimeout(() => (copiedValue.value = null), 1000);
                Statamic.$toast.success(__('Copied to clipboard'));
            })
            .catch(() => {
                Statamic.$toast.error(__('Unable to copy to clipboard'));
            });
    };

    return {
        copySupported,
        copied,
        isCopied,
        copy,
    };
}
