import { computed, ref, watch } from 'vue';

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
// Under 1000px there's no room for the right sidebar, so it always floats there. The choice is kept, for when the window is wider again.
const prefersFloating = ref(getStored(floatingKey, true));
const narrowQuery = typeof window !== 'undefined' && window.matchMedia ? window.matchMedia('(max-width: 999px)') : null;
const narrow = ref(narrowQuery?.matches ?? false);
narrowQuery?.addEventListener('change', (event) => (narrow.value = event.matches));
const floating = computed({
    get: () => prefersFloating.value || narrow.value,
    set: (value) => (prefersFloating.value = value),
});

watch(prefersFloating, (value) => store(floatingKey, value));

// On a phone, the on-screen keyboard covers the page without resizing it, so the floating chat (fixed to the bottom of a page that's now partly hidden) ends up behind it.
// So keep track of how much of the page the keyboard covers, for the chat and Ask to sit above it (see PanelLayout.vue). Pinch zooming also shrinks what's visible, but that's not a keyboard.
const visible = typeof window !== 'undefined' ? window.visualViewport : null;
function measureKeyboard() {
    const zoomed = visible.scale > 1.01;
    const covered = zoomed ? 0 : Math.max(0, window.innerHeight - visible.height - visible.offsetTop);
    document.documentElement.style.setProperty('--ai-keyboard', `${Math.round(covered)}px`);
    document.documentElement.style.setProperty('--ai-visible-height', covered ? `${Math.round(visible.height)}px` : '100dvh');
}
if (visible) {
    visible.addEventListener('resize', measureKeyboard);
    visible.addEventListener('scroll', measureKeyboard);
    measureKeyboard();
}

// Bumped to ask the chat to focus its input when it's already open.
const focusRequests = ref(0);
// Prototype: a request for the chat to play out, from the demo buttons at the bottom of the nav (see AiChatDemos.vue)
const demoRequest = ref(null);
// Prototype: a request to start (or stop) dictating, from ⌘⇧D anywhere (see AiChatButton.vue). The chat picks it up, even as it opens.
const dictateRequest = ref(false);
// Prototype: whether phase 2's features (dictating) are showing. They're only for demoing: a phase 2 demo switches them on, and a
// phase 1 demo (or reloading the page) switches them off again.
const phaseTwo = ref(false);
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

    // preventScroll, so a field that's scrolled out of view gets focus back without the page jumping to it
    if (el && document.contains(el)) el.focus({ preventScroll: true });
    else document.activeElement?.blur?.();
}

export default function useAiChat() {
    return {
        open,
        messages,
        floating,
        focusRequests,
        demoRequest,
        dictateRequest,
        phaseTwo,
        toggle: () => {
            if (!open.value) rememberFocus();
            open.value = !open.value;
        },
        close: () => (open.value = false),
        // Start over: empty the thread and put the cursor back in the input.
        newChat: () => {
            messages.value = [];
            focusRequests.value++;
        },
        leave,
        // Open the chat if it's closed, or just focus it if it's open, and never close it. For coming from the command palette.
        focus: () => {
            if (!open.value) {
                rememberFocus();
                open.value = true;
            } else {
                focusRequests.value++;
            }
        },
        // Prototype: open the chat if it's closed, and start dictating into it (or stop, if you already are)
        dictate: () => {
            if (!open.value) rememberFocus();
            open.value = true;
            dictateRequest.value = true;
        },
        // Prototype: start a new chat and type the request into it, as if you had, or dictate it (see AiChat.vue)
        demo: (text, { dictate = false, phase = 1 } = {}) => {
            phaseTwo.value = phase === 2;
            if (!open.value) rememberFocus();
            open.value = true;
            messages.value = [];
            demoRequest.value = { text, dictate };
        },
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
