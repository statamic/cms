<template>
    <div
        class="@container/panel bg-white dark:bg-gray-850 rounded-xl ring ring-gray-300 dark:ring-x-0 dark:ring-b-0 dark:ring-gray-700 shadow-ui-md"
        :class="[
            sortableItemClass,
            {
                'opacity-50': isExcessive,
                'ring-red-500': hasError,
                'ring-2 ring-blue-400': moving,
            },
        ]"
        :data-error="hasError ?? undefined"
        :data-moving="moving || undefined"
        :aria-grabbed="moving ? 'true' : undefined"
    >
        <header class="bg-gray-50 dark:bg-gray-900 rounded-t-xl border-b border-gray-300 dark:border-gray-700 ps-4 pe-2 py-1.5 flex items-center justify-between">
            <ui-drag-handle :class="{ [sortableHandleClass]: grid.isReorderable }" />
            <div v-if="showRowControls" class="flex flex-1 items-end justify-end">
                <button
                    ref="rootEl"
                    type="button"
                    class="sr-only"
                    :tabindex="moving ? 0 : -1"
                    :aria-label="__('Use up and down arrows to reorder. Press Enter or Escape when finished.')"
                />
                <Dropdown placement="left-start">
                    <DropdownMenu>
                        <DropdownItem
                            v-if="grid.isReorderable"
                            :text="__('Move')"
                            icon="handles"
                            @click="startMoving"
                        />
                        <DropdownItem v-if="canAddRows" :text="__('Duplicate Row')" icon="duplicate" @click="$emit('duplicate', index)" />
                        <DropdownItem v-if="canDelete" :text="__('Delete Row')" icon="trash" variant="destructive" @click="$emit('removed', index)" />
                    </DropdownMenu>
                </Dropdown>
                <div class="sr-only" aria-live="assertive">{{ status }}</div>
            </div>
        </header>
        <div class="px-4 py-3">
            <FieldsProvider
                :fields="fields"
                :as-config="false"
                :read-only
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
import { Dropdown, DropdownMenu, DropdownItem, PublishFields, PublishFieldsProvider as FieldsProvider } from '@ui';

export default {
    mixins: [Row],

    components: { Dropdown, DropdownMenu, DropdownItem, PublishFields, FieldsProvider },
};
</script>
