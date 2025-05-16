<template>
    <div
        class="replicator-set dark:border-dark-900 mb-4 rounded-sm border shadow-sm"
        :class="[sortableItemClass, { 'opacity-50': isExcessive }]"
    >
        <div class="replicator-set-header">
            <div class="item-move sortable-handle cursor-grab" :class="{ [sortableHandleClass]: grid.isReorderable }" />
            <div class="replicator-set-header-inner flex w-full items-end justify-end py-2 ltr:pl-2 rtl:pr-2">
                <button
                    v-if="canAddRows"
                    class="group flex items-center self-end ltr:mr-2 rtl:ml-2"
                    @click="$emit('duplicate', index)"
                    :aria-label="__('Duplicate Row')"
                >
                    <svg-icon name="light/duplicate" class="h-4 w-4 text-gray-600 group-hover:text-gray-900" />
                </button>
                <button
                    v-if="canDelete"
                    class="group flex items-center self-end"
                    @click="$emit('removed', index)"
                    :aria-label="__('Delete Row')"
                >
                    <svg-icon name="micro/trash" class="h-4 w-4 text-gray-600 group-hover:text-gray-900" />
                </button>
            </div>
        </div>

        <FieldsProvider
            :fields="fields"
            :path-prefix="`${fieldPathPrefix}.${index}`"
            :meta-path-prefix="`${fieldPathPrefix}.existing.${values._id}`"
        >
            <PublishFields />
        </FieldsProvider>
    </div>
</template>

<script>
import Row from './Row.vue';
import SetField from '../replicator/Field.vue';
import { ValidatesFieldConditions } from '../../field-conditions/FieldConditions.js';
import { default as PublishFields } from '@statamic/components/ui/Publish/Fields.vue';
import FieldsProvider from '@statamic/components/ui/Publish/FieldsProvider.vue';

export default {
    mixins: [Row, ValidatesFieldConditions],

    components: { SetField, PublishFields, FieldsProvider },
};
</script>
