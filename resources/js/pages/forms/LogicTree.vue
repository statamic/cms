<script setup>
import { Icon } from '@ui';
import { computed } from 'vue';

const props = defineProps({
    pages: { type: Array, required: true },
    fields: { type: Array, required: true },
    suggestableFields: { type: Array, required: true },
    fieldtypes: Array,
});

const pagesWithFields = computed(() => {
    return props.pages.map((page, index) => ({
        ...page,
        title: page.display || __('Page :current of :total', { current: index + 1, total: props.pages.length }),
        fields: props.fields.filter((field) => field.page_index === index),
    }));
});
</script>

<template>
    <div class="space-y-8">
        <section
            v-for="page in pagesWithFields"
            :key="page._id"
            class="space-y-3"
        >
            <div class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                <Icon name="page" class="size-4 shrink-0 text-gray-500 dark:text-gray-400" aria-hidden="true" />
                <span>{{ __(page.title) }}</span>
            </div>

            <ul class="space-y-2">
                <li
                    v-for="field in page.fields"
                    :key="field._id"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 dark:border-white/10 dark:bg-gray-900 dark:text-gray-100"
                >
                    {{ field.display }}
                </li>
            </ul>
        </section>
    </div>
</template>
