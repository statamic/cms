<template>
    <div
        class="shadow-ui-sm relative z-(--z-index-above) flex w-full h-full items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 [&:has(.cursor-grab)]:px-1.5 py-1.5 mb-1.5 last:mb-0 text-base dark:border-gray-700 dark:with-contrast:border-gray-500 dark:bg-gray-900"
    >
        <ui-icon name="handles" class="item-move sortable-handle size-4 cursor-grab text-gray-300 dark:text-gray-700" v-if="sortable" />
        <div class="flex flex-1 items-center line-clamp-1 text-sm text-gray-600 dark:text-gray-300">
            <div
                v-if="item.invalid"
                v-tooltip.top="__('messages.relationship_item_unavailable')"
                v-text="__(item.title)"
                class="line-clamp-1 text-sm text-gray-500 dark:text-gray-400"
            />

            <div v-else v-text="__(item.title)" />

            <div class="flex flex-1 items-center justify-end">
                <Button
                    v-if="formItem.hasSubmissions()"
                    size="xs"
                    variant="ghost"
                    icon="eye"
                    :text="__('View Submissions')"
                    @click="formItem.viewSubmissions()"
                />

                <div class="flex items-center" v-if="!readOnly">
                    <Dropdown>
                        <template #trigger>
                            <Button icon="dots" variant="ghost" size="xs" v-bind="$attrs" :aria-label="__('Open dropdown menu')" />
                        </template>
                        <DropdownMenu>
                            <DropdownItem
                                v-if="formItem.hasConfigure()"
                                :text="__('Configure')"
                                @click="formItem.configure()"
                            />
                            <DropdownItem
                                :text="__('Unlink')"
                                variant="destructive"
                                @click="$emit('removed')"
                            />
                        </DropdownMenu>
                    </Dropdown>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Button, Dropdown, DropdownMenu, DropdownItem } from '@/components/ui';

export default {
    components: {
        Button,
        DropdownItem,
        DropdownMenu,
        Dropdown,
    },

    inject: {
        formItem: {
            from: 'formFieldtypeItem',
        },
    },

    props: {
        item: Object,
        config: Object,
        statusIcon: Boolean,
        editable: Boolean,
        sortable: Boolean,
        readOnly: Boolean,
        formComponent: String,
        formComponentProps: Object,
        formStackSize: String,
    },

    emits: ['removed'],
};
</script>
