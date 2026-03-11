import { ref, watch } from 'vue';

const STORAGE_KEY = 'statamic.max-width-enabled';
const isMaxWidthEnabled = ref(
    localStorage.getItem(STORAGE_KEY) !== 'false' // Default to true
);

// Sync with localStorage
watch(isMaxWidthEnabled, (value) => {
    localStorage.setItem(STORAGE_KEY, value.toString());
});

export default function useMaxWidthToggle() {
    const toggle = () => {
        isMaxWidthEnabled.value = !isMaxWidthEnabled.value;
    };

    return {
        isMaxWidthEnabled,
        toggle,
    };
}
