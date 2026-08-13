<template>
    <div
        class="shadow-ui-sm relative z-(--z-index-above) flex w-full h-full items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 [&:has(.cursor-grab)]:px-1.5 py-1.5 mb-1.5 last:mb-0 text-base dark:border-gray-700 dark:with-contrast:border-gray-500 dark:bg-gray-900"
    >
        <ui-icon name="handles" class="item-move sortable-handle size-4 shrink-0 cursor-grab text-gray-300 dark:text-gray-700" v-if="sortable" />
        <ui-status-indicator v-if="item.status" :status="item.status" class="shrink-0" />

        <div class="flex min-w-0 flex-1 flex-wrap items-baseline gap-x-1.5 gap-y-0.5 text-sm text-gray-600 dark:text-gray-300">
            <div
                v-if="item.invalid"
                v-tooltip.top="__('messages.relationship_item_unavailable')"
                v-text="__(item.title)"
                class="text-sm text-gray-500 dark:text-gray-400"
            />

            <template v-else>
                <span
                    v-if="item.hint"
                    v-text="item.hint"
                    :title="item.hint"
                    class="text-xs text-gray-400 dark:text-gray-500"
                />
                <span v-if="item.hint" class="text-xs text-gray-300 dark:text-gray-600" aria-hidden="true">»</span>

                <a
                    v-if="editable"
                    @click.prevent="edit"
                    v-text="__(item.title)"
                    class="text-sm text-gray-600 dark:text-gray-300"
                    v-tooltip="item.title"
                    :href="item.edit_url"
                />

                <div v-else v-text="__(item.title)" />
            </template>

            <inline-edit-form
                v-if="isEditing"
                :item="item"
                :component="formComponent"
                :component-props="formComponentProps"
                :stack-size="formStackSize"
                @updated="itemUpdated"
                @closed="isEditing = false"
            />
        </div>

        <div class="flex shrink-0 items-center" v-if="!readOnly">
            <Dropdown>
                <template #trigger>
                    <Button icon="dots" variant="ghost" size="xs" v-bind="$attrs" :aria-label="__('Open dropdown menu')" />
                </template>
                <DropdownMenu>
                    <DropdownItem
                        v-if="editable"
                        :text="__('Edit')"
                        @click="edit"
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
</template>

<script>
import { getActivePinia } from 'pinia';
import InlineEditForm from './InlineEditForm.vue';
import { Button, Dropdown, DropdownMenu, DropdownItem, publishContextKey as containerContextKey } from '@/components/ui';

export default {
    components: {
        Button,
        DropdownItem,
        DropdownMenu,
        Dropdown,
        InlineEditForm,
    },

    inject: {
        publishContainer: {
            from: containerContextKey,
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

    data() {
        return {
            isEditing: false,
        };
    },

    methods: {
        edit() {
            if (!this.editable) return;
            if (this.item.invalid) return;

            if (this.item.reference) {
                let parentContainer = this.publishContainer.parentContainer;
                while (parentContainer) {
                    if (parentContainer.reference.value === this.item.reference) {
                        this.$toast.error(__("You're already editing this item."));
                        return;
                    }
                    parentContainer = parentContainer.parentContainer;
                }
            }

            this.isEditing = true;
        },

        itemUpdated(responseData) {
            this.item.title = responseData.title;
            this.item.published = responseData.published;
            this.item.private = responseData.private;
            this.item.status = responseData.status;

            this.$events.$emit(`live-preview.${this.publishContainer.name.value}.refresh`);
        },
    },
};
</script>
