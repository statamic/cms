import { ref } from 'vue';

// Module scoped so the chat stays open (and keeps its messages) across Inertia navigation.
const open = ref(false);
const messages = ref([]);

export default function useAiChat() {
    return {
        open,
        messages,
        toggle: () => (open.value = !open.value),
        close: () => (open.value = false),
    };
}
