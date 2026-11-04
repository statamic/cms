import { ref } from 'vue';

// Prototype: which fieldtypes can have AI suggestions switched on in the blueprint. Their values are plain text, so a suggestion can be dropped straight in.
export const suggestableTypes = ['text', 'textarea', 'markdown'];

// The icon on the suggest button and the blueprint toggle
export const icon = 'ai-edit';

// Prototype: the "Suggest down" demos (see AiChatDemos.vue) switches this on, so the next suggestions come back as a failure
export const suggestionsFail = ref(false);
