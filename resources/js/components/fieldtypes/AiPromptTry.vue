<script setup>
import { computed, ref } from 'vue';
import { Button, injectPublishContext } from '@ui';
import AiPromptTryRun from './AiPromptTryRun.vue';
import { data_get } from '@/util/data_get.js';

// Prototype: "Try it" on each saved prompt on Settings → AI. It runs the prompt as it's written now, before it's saved.
const props = defineProps({
    /** Where this prompt's values are in the form, like prompts.2 */
    fieldPathPrefix: { type: String, required: true },
    meta: { type: Object, default: () => ({}) },
});

const { values } = injectPublishContext();
const prompt = computed(() => data_get(values.value, props.fieldPathPrefix) || {});

const shown = ref(false);
</script>

<template>
    <!-- Only a button until it's wanted, so the prompts stay quiet. Pressing it opens what to try it on in its place, under the instructions, and tries it straight away. -->
    <Button v-if="!shown" size="sm" icon="ai-chat" :text="__('Try it')" @click="shown = true" />
    <AiPromptTryRun v-else :prompt="prompt" :entries="meta.entries" />
</template>
