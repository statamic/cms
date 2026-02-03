import { ref, computed } from 'vue';

export default function useCopy() {
    const isSupported = 'clipboard' in navigator && typeof navigator.clipboard.writeText === 'function';
    const copiedValue = ref(null);
    const copied = computed(() => copiedValue.value !== null);
    const isCopied = (value) => copiedValue.value === value;

    const copy = (value) => {
        if (!value) return;

        navigator.clipboard
            .writeText(value)
            .then(() => {
                copiedValue.value = value;
                setTimeout(() => (copiedValue.value = null), 1000);
                Statamic.$toast.success(__('Copied to clipboard'));
            })
            .catch(() => {
                Statamic.$toast.error(__('Unable to copy to clipboard'));
            });
    };

    return {
        isSupported,
        copied,
        isCopied,
        copy,
    };
}