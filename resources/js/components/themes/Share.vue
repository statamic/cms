<script setup lang="ts">
import { Theme } from './types';
import { Input, Button, CardPanel } from '@ui';
import { computed, ref } from 'vue';
import axios from 'axios';
import { cp_url } from '@/bootstrap/globals';
import { translate as __ } from '@/translations/translator';
import { toast } from '@api';

const emit = defineEmits<{
    (e: 'shared'): void
}>();

const props = defineProps<{
    theme: Theme
}>();

const name = ref('My Custom Theme');

const payload = computed(() => {
    return {
        name: name.value,
        colors: props.theme.colors,
        darkColors: props.theme.darkColors
    };
});

function share() {
    axios.post(cp_url('themes/share'), payload.value)
        .then(response => {
            window.open(response.data.url, '_blank');
            emit('shared');
        })
        .catch((e) => {
            console.error(e);
            toast.error('Failed to share the theme. Please try again later.');
        });
}
</script>

<template>
    <CardPanel
        :heading="__('Publish')"
        :subheading="__('messages.theme_share_instructions')"
    >
        <Input v-model="name" />
        <Button :text="`${__('Publish')}...`" @click="share" class="w-full" />
    </CardPanel>
</template>
