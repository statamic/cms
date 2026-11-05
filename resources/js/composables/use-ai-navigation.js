import { shallowRef } from 'vue';

// Prototype: the open navigation's page sets this while it's showing, so the AI chat can add a link to its tree (see pages/navigation/Show.vue and
// the navigation scenario in components/ai-chat/mock-runs.js). The link is added unsaved, the same as if you'd added it yourself.
export const aiNavigation = shallowRef(null);
