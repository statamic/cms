<script setup>
import { Card, Icon } from '@ui';
import { Link } from '@inertiajs/vue3';
import { useSlots, computed } from 'vue';

const props = defineProps({
    title: {type: String },
    icon: { type: String },
    href: { type: String },
});

const slots = useSlots();
const hasHeader = computed(() => Boolean(props.title || props.icon || slots.actions));

</script>

<template>
    <Card inset class="@container/widget min-h-54" v-cloak>
        <div class="flex h-full min-h-54 flex-col">
            <header v-if="hasHeader" class="flex items-center min-h-[49px] justify-between border-b border-gray-200 px-4.5 py-2 dark:border-gray-700">
                <component :is="href ? Link : 'div'" class="flex items-center gap-2 sm:gap-3" :href>
                    <Icon v-if="icon" :name="icon" class="hidden! size-5 text-gray-500 @xs/widget:block!" />
                    <span v-if="title" v-text="title" />
                </component>
                <div class="flex items-center gap-4 -mr-2.5 starting-style-transition">
                    <slot name="actions" />
                </div>
            </header>
            <slot />
            <slot name="footer" />
        </div>
    </Card>
</template>
