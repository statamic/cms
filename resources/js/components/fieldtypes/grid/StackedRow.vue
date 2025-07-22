<template>
    <div
        class="bg-white dark:bg-gray-850 rounded-xl ring ring-gray-300 dark:ring-x-0 dark:ring-b-0 dark:ring-gray-700 shadow-ui-md"
        :class="[sortableItemClass, { 'opacity-50': isExcessive }]"
    >
        <header class="bg-gray-50 dark:bg-gray-900 rounded-t-xl border-b border-gray-300 dark:border-gray-700 ps-4 pe-2 py-1.5 flex items-center justify-between">
            <ui-drag-handle :class="{ [sortableHandleClass]: grid.isReorderable }" />
            <div class="flex flex-1 items-end justify-end">
                <ui-button
                    v-if="canAddRows"
                    @click="$emit('duplicate', index)"
                    v-tooltip="__('Duplicate Row')"
                    icon="duplicate"
                    variant="ghost"
                    inset
                    size="sm"
                />
                <ui-button
                    v-if="canDelete"
                    @click="$emit('removed', index)"
                    v-tooltip="__('Delete Row')"
                    icon="trash"
                    variant="ghost"
                    inset
                    size="sm"
                />
            </div>
        </header>
        <div class="px-4 py-3">
            <FieldsProvider
                :fields="fields"
                :field-path-prefix="`${fieldPathPrefix}.${index}`"
                :meta-path-prefix="`${metaPathPrefix}.existing.${values._id}`"
            >
                <PublishFields />
            </FieldsProvider>
        </div>
    </div>
</template>

<script>
import Row from './Row.vue';
import { ValidatesFieldConditions } from '../../field-conditions/FieldConditions.js';
import { default as PublishFields } from '@statamic/components/ui/Publish/Fields.vue';
import FieldsProvider from '@statamic/components/ui/Publish/FieldsProvider.vue';

export default {
    mixins: [Row, ValidatesFieldConditions],

    components: { PublishFields, FieldsProvider },
};
</script>
