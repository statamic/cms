import { ref, watch } from 'vue';

const floatingKey = 'statamic.ai-chat.floating';

function getStored(key, fallback) {
    try {
        const value = localStorage.getItem(key);
        return value === null ? fallback : value === 'true';
    } catch {
        return fallback;
    }
}

function store(key, value) {
    try {
        localStorage.setItem(key, String(value));
    } catch {}
}

// Module scoped so the chat stays open (and keeps its messages) across Inertia navigation.
const open = ref(false);
const messages = ref([]);
// Prototype: float the chat in the bottom right corner instead of docking it in the right sidebar.
const floating = ref(getStored(floatingKey, true));

watch(floating, (value) => store(floatingKey, value));

export default function useAiChat() {
    return {
        open,
        messages,
        floating,
        toggle: () => (open.value = !open.value),
        close: () => (open.value = false),
    };
}
