<script setup>
import { computed, nextTick, onMounted, ref, useTemplateRef } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import { Button, Field, Input, Textarea } from '@ui';
import { pretendSave } from './mock-runs.js';

// Prototype: a new saved prompt, made from the chat's / menu rather than on Settings → AI. Just a name and instructions: it's offered everywhere,
// to everyone, like a new one on Settings → AI, which is still where to limit it to some collections or roles. The image's AI card (see
// AiImage.vue) makes its image skills with it too, so the two work the same way: given startsFrom, it's an image skill.
const props = defineProps({
    /** What was typed after the /, if it came from a command that matched nothing. It becomes the name, so "/legal" starts as "Legal". */
    from: { type: String, default: '' },
    /** Prototype: the Create a prompt demo's { instructions }, which it types in and then pretends to save (see AiChatDemos.vue) */
    demo: { type: Object, default: null },
    /** For an image skill, what it starts from (one of the image model's skills, or "edit"). Without it, it's a chat skill. */
    startsFrom: { type: String, default: null },
});

const emit = defineEmits(['saved', 'cancel']);

const name = ref(props.from ? props.from.replace(/-/g, ' ').replace(/^./, (letter) => letter.toUpperCase()) : '');
const instructions = ref('');
const isImage = props.startsFrom !== null;
const errors = ref({});
const saving = ref(false);

// Its command is its name as a slug, as on Settings → AI
const command = computed(() =>
    name.value
        .toLowerCase()
        .normalize('NFKD')
        .replace(/[̀-ͯ]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, ''),
);

const root = useTemplateRef('root');

// Straight into the name, or into the instructions when the name came from the command
onMounted(async () => {
    await nextTick();
    root.value?.querySelector(props.from ? 'textarea' : 'input')?.focus();
    if (props.demo) playDemo();
});

// Prototype: the demo types the instructions in, as if you had, then saves
const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
async function playDemo() {
    await wait(700);
    const text = props.demo.instructions;
    for (let typed = 0; typed < text.length; ) {
        typed = Math.min(text.length, typed + 2);
        instructions.value = text.slice(0, typed);
        await wait(30);
    }
    await wait(900);
    save();
}

const page = usePage();

async function save() {
    if (saving.value) return;
    saving.value = true;
    errors.value = {};

    // The demo's save is only pretend, so nothing is left in Settings → AI and it can be played again. Its prompt is marked, so the
    // next play takes it away first (see AiChat.vue).
    if (props.demo || pretendSave.value) {
        await wait(600);
        if (isImage) {
            const prompt = { name: name.value, command: command.value, instructions: instructions.value, starts_from: props.startsFrom, makes: 2, shape: 'original', containers: [], demo: true };
            page.props._statamic.aiImagePrompts = [...(page.props._statamic.aiImagePrompts ?? []), prompt];
        } else {
            const prompt = { name: name.value, command: command.value, instructions: instructions.value, collections: [], use_on: 'page', demo: true };
            page.props._statamic.aiPrompts = [...(page.props._statamic.aiPrompts ?? []), prompt];
        }
        saving.value = false;
        return emit('saved', command.value);
    }

    try {
        // The prompts come with every page, so swap in the new list rather than waiting for the next one
        if (isImage) {
            const { data } = await axios.post(cp_url('ai/image-prompts'), { name: name.value, instructions: instructions.value, starts_from: props.startsFrom });
            page.props._statamic.aiImagePrompts = data.imagePrompts;
            return emit('saved', data.command);
        }
        const { data } = await axios.post(cp_url('ai/prompts'), { name: name.value, instructions: instructions.value });
        page.props._statamic.aiPrompts = data.prompts;
        emit('saved', data.command);
    } catch (error) {
        errors.value = error.response?.data?.errors ?? { name: [__('Something went wrong')] };
    } finally {
        saving.value = false;
    }
}

// The chat's own keys (Enter sends, / opens the help…) are for its input, so keep them in here. Esc goes back, ⌘Enter saves, and Enter in the name moves on to the instructions.
function onKeydown(event) {
    event.stopPropagation();

    if (event.key === 'Escape') {
        event.preventDefault();
        emit('cancel');
    } else if (event.key === 'Enter' && (event.metaKey || event.ctrlKey)) {
        event.preventDefault();
        save();
    } else if (event.key === 'Enter' && event.target.tagName === 'INPUT') {
        event.preventDefault();
        root.value?.querySelector('textarea')?.focus();
    }
}
</script>

<template>
    <!-- A heading like the saved prompts', then the fields, then the buttons -->
    <div ref="root" class="space-y-2 p-2" @keydown="onKeydown">
        <p class="px-1 text-xs text-gray-500 dark:text-gray-400">{{ __('New skill') }}</p>

        <!-- No card round the fields: the popup is already white and the inputs have their own borders -->
        <div class="space-y-3 px-1 py-1">
            <Field :label="__('Name')" :error="errors.name?.[0]">
                <Input v-model="name" size="sm" :placeholder="isImage ? __('Moody black and white') : __('Legal check')" />
            </Field>
            <Field :label="__('Instructions')" :error="errors.instructions?.[0]">
                <Textarea v-model="instructions" rows="3" :placeholder="isImage ? __('What to change, and how it should look.') : __('What the AI should do, as you would ask a colleague.')" />
            </Field>
            <!-- What you'll type to run it, so you know before saving -->
            <p class="flex min-h-5 items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                <template v-if="command">
                    {{ __('Run it with') }}
                    <span class="inline-flex h-5 items-center rounded-md bg-gray-100 px-1.5 text-2xs font-medium text-gray-600 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">/{{ command }}</span>
                </template>
                <template v-else>{{ __('Offered everywhere. Limit it on Settings → AI.') }}</template>
            </p>
        </div>

        <div class="flex items-center justify-end gap-2 px-1">
            <Button size="sm" variant="ghost" :text="__('Cancel')" @click="emit('cancel')" />
            <Button size="sm" variant="primary" :text="__('Save')" :loading="saving" :disabled="!name.trim() || !instructions.trim()" @click="save" />
        </div>
    </div>
</template>
