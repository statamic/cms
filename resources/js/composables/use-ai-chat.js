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

// Bumped to ask the chat to focus its input when it's already open.
const focusRequests = ref(0);
// Where focus was before it moved into the chat, so Esc and the shortcut can hand it back.
let previousFocus = null;

const isInChat = () => !!document.activeElement?.closest?.('[data-ai-chat]');

function rememberFocus() {
    const el = document.activeElement;
    if (el && el !== document.body && !el.closest?.('[data-ai-chat]')) previousFocus = el;
}

function leave() {
    const el = previousFocus;
    previousFocus = null;

    if (el && document.contains(el)) el.focus();
    else document.activeElement?.blur?.();
}

export default function useAiChat() {
    return {
        open,
        messages,
        floating,
        focusRequests,
        toggle: () => {
            if (!open.value) rememberFocus();
            open.value = !open.value;
        },
        close: () => (open.value = false),
        leave,
        // The keyboard shortcut: closed opens (and focuses), open but unfocused focuses, focused closes.
        shortcut: () => {
            if (!open.value) {
                rememberFocus();
                open.value = true;
            } else if (!isInChat()) {
                rememberFocus();
                focusRequests.value++;
            } else {
                open.value = false;
                leave();
            }
        },
    };
}
