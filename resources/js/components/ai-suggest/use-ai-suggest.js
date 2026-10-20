import { computed, ref, watch } from 'vue';

// Prototype: which fieldtypes can have AI suggestions switched on in the blueprint. Their values are plain text, so a suggestion can be dropped straight in.
export const suggestableTypes = ['text', 'textarea', 'markdown'];

// The icon on the suggest button and the blueprint toggle
export const icon = 'ai-edit';

// Prototype: how a hovered or focused suggestion is previewed in the field. Flip through them with the toggle in the bottom left.
export const previewStyles = [
    { key: 'grey', label: 'Grey text' },
    { key: 'tinted', label: 'Normal text on a pale yellow field' },
    { key: 'diff', label: 'With the changes marked, like the suggestions' },
    { key: 'italic', label: 'Grey italic text, accent-coloured edge' },
];

const storageKey = 'statamic.ai-suggest.preview-style';

const previewStyleIndex = ref((() => {
    try {
        return Math.min(Number(localStorage.getItem(storageKey)) || 0, previewStyles.length - 1);
    } catch {
        return 0;
    }
})());

watch(previewStyleIndex, (value) => {
    try {
        localStorage.setItem(storageKey, String(value));
    } catch {}
});

const previewStyle = computed(() => previewStyles[previewStyleIndex.value].key);

// How many suggest buttons are on the page, so the toggle only shows when there's something to compare
const mounted = ref(0);

export default function useAiSuggest() {
    return { previewStyleIndex, previewStyles, previewStyle, mounted };
}
