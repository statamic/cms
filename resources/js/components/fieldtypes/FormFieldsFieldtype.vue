<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { Combobox, Icon } from '@/components/ui';
import { categories, categoryColorClasses } from '@/components/forms/builder/categories.js';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { isReadOnly, update } = Fieldtype.use(emit, props);

const options = computed(() => props.meta.options ?? []);

const category = (handle) => categories[handle] ?? categories.other;
const categoryTitle = (handle) => category(handle).title;
const iconClasses = (handle) => `size-4 shrink-0 ${categoryColorClasses[category(handle).color].icon}`;
</script>

<template>
    <Combobox
        :id
        :clearable="config.clearable"
        :disabled="config.disabled"
        :max-selections="config.max_items"
        :model-value="value"
        :multiple="config.multiple"
        :options
        :placeholder="__(config.placeholder)"
        :read-only="isReadOnly"
        :searchable="(config.searchable ?? true) || config.taggable"
        :taggable="config.taggable"
        :close-on-select="(config.taggable && !options.length) || !config.multiple"
        @update:modelValue="update"
    >
        <template #option="option">
            <span class="flex w-full items-center gap-2">
                <Icon v-if="option.icon" :name="option.icon" :class="iconClasses(option.category)" />
                <span class="truncate">{{ __(option.label) }}</span>
                <span v-if="option.category" class="ms-auto text-2xs text-gray-500 dark:text-gray-400">
                    {{ categoryTitle(option.category) }}
                </span>
            </span>
        </template>
        <template #selected-option="{ option }">
            <span class="flex items-center gap-2 truncate">
                <Icon v-if="option.icon" :name="option.icon" :class="iconClasses(option.category)" />
                <span class="truncate">{{ __(option.label) }}</span>
            </span>
        </template>
    </Combobox>
</template>
