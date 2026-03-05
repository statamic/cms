import { reactive } from 'vue';

const STORAGE_KEY = 'statamic.clipboard';
const DEFAULT_TTL = 10 * 60 * 1000;

const state = reactive({
    data: null,
    expiresAt: null,
});

function loadFromStorage() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) {
            state.data = null;
            state.expiresAt = null;
            return;
        }

        const parsed = JSON.parse(raw);
        state.expiresAt = parsed.expiresAt;
        state.data = parsed.payload;
    } catch {
        state.data = null;
        state.expiresAt = null;
    }
}

function isExpired() {
    return !state.expiresAt || Date.now() > state.expiresAt;
}

loadFromStorage();

window.addEventListener('storage', (event) => {
    if (event.key === STORAGE_KEY) {
        loadFromStorage();
    }
});

export default function useClipboard() {
    const get = () => {
        if (isExpired()) {
            return null;
        }

        return state.data;
    };

    const set = (type, items, ttl = DEFAULT_TTL) => {
        const expiresAt = Date.now() + ttl;
        localStorage.setItem(STORAGE_KEY, JSON.stringify({ expiresAt, payload: { type, items } }));
        state.expiresAt = expiresAt;
        state.data = { type, items };
    };

    const clear = () => {
        localStorage.removeItem(STORAGE_KEY);
        state.data = null;
        state.expiresAt = null;
    };

    const canPaste = (type, allowedHashes) => {
        if (isExpired() || !state.data || state.data.type !== type) {
            return false;
        }

        return state.data.items.every((item) => allowedHashes.includes(item.configHash));
    };

    return { state, get, set, clear, canPaste };
}
