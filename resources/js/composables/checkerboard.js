import { ref, computed, watch } from 'vue';
import { preferences } from '@api';

const PREFERENCE_KEY = 'assets.browser_checkerboard_mode';
const DEFAULT_MODE = 'transparent';
const CHECKERBOARD_MODES = ['light', 'dark', 'transparent'];

let checkerboardState = null;

function normalizeMode(raw) {
    return CHECKERBOARD_MODES.includes(raw) ? raw : DEFAULT_MODE;
}

export default function useCheckerboard() {
    if (checkerboardState) {
        return checkerboardState;
    }

    const mode = ref(normalizeMode(preferences.get(PREFERENCE_KEY, DEFAULT_MODE)));

    watch(mode, (value) => preferences.set(PREFERENCE_KEY, value === 'transparent' ? null : value));

    const nextMode = computed(() => {
        const i = CHECKERBOARD_MODES.indexOf(mode.value);
        return CHECKERBOARD_MODES[(i >= 0 ? i + 1 : CHECKERBOARD_MODES.length) % CHECKERBOARD_MODES.length];
    });

    const enabled = computed(() => mode.value !== 'transparent');

    const icon = computed(() => {
        if (mode.value === 'light') return 'sun';
        if (mode.value === 'dark') return 'moon';
        return 'eye-slash';
    });

    function cycle() {
        mode.value = nextMode.value;
    }

    checkerboardState = {
        mode,
        enabled,
        icon,
        cycle,
    };

    return checkerboardState;
}
