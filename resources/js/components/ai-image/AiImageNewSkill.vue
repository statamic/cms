<script setup>
import { computed, nextTick, onMounted, ref, useTemplateRef } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import { Button, Field, Input, Textarea } from '@ui';

// Prototype: "Save as skill", in the image's AI card (see AiImage.vue): a request that worked, kept as an image skill, as the chat's
// "Create a skill" keeps a prompt (see AiChatNewPrompt.vue). It's filled in from what you asked: the instructions are what you typed, and
// it starts from what that request ran. Like a new skill on Settings → AI, it's offered everywhere, to everyone, makes two images the
// image's shape, and Settings → AI is where to change that, or add pictures of the look it's after.
const props = defineProps({
    /** What you asked: { instructions, startsFrom } */
    from: { type: Object, required: true },
    /** What the image model can do, which it can start from, as { command, name } */
    builtIn: { type: Array, required: true },
});

const emit = defineEmits(['saved', 'cancel']);

const name = ref('');
const instructions = ref(props.from.instructions);
const startsFrom = ref(props.from.startsFrom);
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
onMounted(() => nextTick(() => root.value?.querySelector('input')?.focus()));

const page = usePage();

async function save() {
    if (saving.value || !name.value.trim() || !instructions.value.trim()) return;
    saving.value = true;
    errors.value = {};

    try {
        const { data } = await axios.post(cp_url('ai/image-prompts'), { name: name.value, instructions: instructions.value, starts_from: startsFrom.value });
        // The image skills come with every page, so swap in the new list rather than waiting for the next one
        page.props._statamic.aiImagePrompts = data.imagePrompts;
        emit('saved', data.command);
    } catch (error) {
        errors.value = error.response?.data?.errors ?? { name: [__('Something went wrong')] };
    } finally {
        saving.value = false;
    }
}

// The card's own keys (Enter sends, / opens the skills…) are for its input, so keep them in here. Esc goes back, ⌘Enter saves, and Enter in
// the name moves on to the instructions.
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
    <!-- Laid out like the chat's new skill: a heading, the fields, then the buttons. It takes the card's dark look. -->
    <div ref="root" class="space-y-2 p-2" @keydown="onKeydown">
        <p class="px-1 text-2xs text-gray-400">{{ __('New image skill') }}</p>

        <div class="space-y-3 px-1 py-1">
            <Field :label="__('Name')" :error="errors.name?.[0]">
                <Input v-model="name" size="sm" :placeholder="__('Brand hero')" />
            </Field>
            <Field :label="__('Instructions')" :error="errors.instructions?.[0]">
                <Textarea v-model="instructions" rows="3" :placeholder="__('What to change, and how it should look.')" />
            </Field>
            <!-- A native select: it gets the system's own menu, keyboard handling and accessibility -->
            <Field :label="__('Starts from')" id="ai-image-new-skill-starts-from">
                <select
                    id="ai-image-new-skill-starts-from"
                    v-model="startsFrom"
                    class="h-8 w-full rounded-lg border border-gray-700 bg-gray-900 px-2 text-sm text-gray-200 focus-visible:focus-outline"
                >
                    <option value="edit">{{ __('An edit, as described') }}</option>
                    <option v-for="skill in builtIn" :key="skill.command" :value="skill.command">{{ skill.name }}</option>
                </select>
            </Field>
            <!-- What you'll type to run it, so you know before saving, and where to set the rest -->
            <p class="flex min-h-5 flex-wrap items-center gap-1.5 text-xs text-gray-400">
                <template v-if="command">
                    {{ __('Run it with') }}
                    <span class="inline-flex h-5 items-center rounded-md bg-gray-800 px-1.5 text-2xs font-medium text-gray-300 ring-1 ring-gray-700">/{{ command }}</span>
                </template>
                <span>{{ __('Set its shape and look on Settings → AI.') }}</span>
            </p>
        </div>

        <div class="flex items-center justify-end gap-2 px-1">
            <Button size="sm" variant="ghost" :text="__('Cancel')" @click="emit('cancel')" />
            <Button size="sm" variant="primary" :text="__('Save')" :loading="saving" :disabled="!name.trim() || !instructions.trim()" @click="save" />
        </div>
    </div>
</template>
