<template>
    <div
        class="relative @container/panel bg-white dark:bg-gray-850 rounded-xl ring ring-gray-300 dark:ring-x-0 dark:ring-b-0 dark:ring-gray-700 shadow-ui-md"
        :class="[
            sortableItemClass,
            {
                'opacity-50': isExcessive,
                'ring-red-500': hasError,
                'focus-outline': moving,
            },
        ]"
        :data-error="hasError ?? undefined"
        :data-moving="moving || undefined"
        :aria-grabbed="moving ? 'true' : undefined"
    >
        <Badge
            v-if="moving"
            size="sm"
            color="blue"
            pill
            class="pointer-events-none absolute start-full top-1/2 ms-2 -translate-y-1/2 gap-0! px-1! py-0.5! [&_svg]:size-2! [&_svg]:opacity-100!"
            aria-hidden="true"
        >
            <span class="flex flex-col items-center -space-y-px">
                <Icon name="chevron-up" />
                <Icon name="chevron-down" />
            </span>
        </Badge>
        <header class="bg-gray-50 dark:bg-gray-900 rounded-t-xl border-b border-gray-300 dark:border-gray-700 ps-4 pe-2 py-1.5 flex items-center justify-between">
            <ui-drag-handle :class="{ [sortableHandleClass]: grid.isReorderable }" />
            <div v-if="showRowControls" class="flex flex-1 items-center justify-end">
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
import { Badge, Dropdown, DropdownMenu, DropdownItem, Icon, PublishFields, PublishFieldsProvider as FieldsProvider } from '@ui';

export default {
    mixins: [Row],

    components: { Badge, Dropdown, DropdownMenu, DropdownItem, Icon, PublishFields, FieldsProvider },
};
</script>
