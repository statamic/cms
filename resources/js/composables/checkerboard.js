import { ref, computed, watch } from 'vue';
import { preferences } from '@api';

const PREFERENCE_KEYS = {
    listing: 'assets.browser_checkerboard_mode',
    editor: 'assets.editor_checkerboard_mode',
};

const DEFAULT_MODE = 'transparent';
const CHECKERBOARD_MODES = ['light', 'dark', 'transparent'];

const stateByContext = {};

function normalizeMode(raw) {
    return CHECKERBOARD_MODES.includes(raw) ? raw : DEFAULT_MODE;
}

/**
 * @param {'listing'|'editor'} [context='listing'] - 'listing' for assets fieldtype and browser grid, 'editor' for asset editor
 */
export default function useCheckerboard(context = 'listing') {
    if (stateByContext[context]) {
        return stateByContext[context];
    }

    const preferenceKey = PREFERENCE_KEYS[context];
    const mode = ref(normalizeMode(preferences.get(preferenceKey, DEFAULT_MODE)));

    watch(mode, (value) => preferences.set(preferenceKey, value === 'transparent' ? null : value));

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

    stateByContext[context] = {
        mode,
        enabled,
        icon,
        cycle,
    };

    return stateByContext[context];
}
