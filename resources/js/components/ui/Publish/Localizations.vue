<script setup>
import { Badge, CardPanel } from '@statamic/ui';

defineProps({
    localizations: {
        type: Array,
        required: true,
    },
    localizing: {
        type: [Boolean, String],
        default: false,
    },
});

defineEmits(['selected']);
</script>

<template>
    <CardPanel v-if="localizations.length > 1" :heading="__('Sites')">
        <div
            v-for="option in localizations"
            :key="option.handle"
            class="-mx-4 flex cursor-pointer items-center px-4 py-2 text-sm"
            :class="option.active ? 'dark:bg-dark-300 bg-blue-100' : 'dark:hover:bg-dark-400 hover:bg-gray-200'"
            @click="$emit('selected', option)"
        >
            <div class="flex flex-1 items-center" :class="{ 'line-through': !option.exists }">
                <span
                    class="little-dot ltr:mr-2 rtl:ml-2"
                    :class="{
                        'bg-green-600': option.published,
                        'bg-gray-500': !option.published,
                        'bg-red-500': !option.exists,
                    }"
                />
                {{ __(option.name) }}
                <loading-graphic :size="14" text="" class="ltr:ml-2 rtl:mr-2" v-if="localizing === option.handle" />
            </div>
            <Badge size="sm" color="orange" v-if="option.origin" :text="__('Origin')" />
            <Badge size="sm" color="blue" v-if="option.active" :text="__('Active')" />
            <Badge size="sm" color="purple" v-if="option.root && !option.origin && !option.active" :text="__('Root')" />
        </div>
    </CardPanel>
</template>
