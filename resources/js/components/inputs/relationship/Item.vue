<template>
    <div
        class="shadow-ui-sm relative z-2 flex w-full h-full items-center gap-2 rounded-lg border border-gray-200 bg-white px-1.5 py-1.5 mb-1.5 last:mb-0 text-base dark:border-x-0 dark:border-t-0 dark:border-white/10 dark:bg-gray-900 dark:inset-shadow-2xs dark:inset-shadow-black"
        :class="{ invalid: item.invalid }"
    >
        <ui-icon name="handles" class="item-move sortable-handle size-4 cursor-grab text-gray-300" v-if="sortable" />
        <div class="flex flex-1 items-center">
            <ui-status-indicator v-if="item.status" :status="item.status" class="me-2" />

            <div
                v-if="item.invalid"
                v-tooltip.top="__('ID not found')"
                v-text="__(item.title)"
                class="line-clamp-1 text-sm text-gray-600 dark:text-gray-300"
            />

            <a
                v-if="!item.invalid && editable"
                @click.prevent="edit"
                v-text="__(item.title)"
                class="line-clamp-1 text-sm text-gray-600 dark:text-gray-300"
                v-tooltip="item.title"
                :href="item.edit_url"
            />

            <div v-if="!item.invalid && !editable" v-text="__(item.title)" />

            <inline-edit-form
                v-if="isEditing"
                :item="item"
                :component="formComponent"
                :component-props="formComponentProps"
                :stack-size="formStackSize"
                @updated="itemUpdated"
                @closed="isEditing = false"
            />

            <div class="flex flex-1 items-center justify-end">
                <div
                    v-if="item.hint"
                    v-text="item.hint"
                    class="text-2xs tracking-tight me-2 hidden whitespace-nowrap text-gray-500 @sm:block"
                />

                <div class="flex items-center" v-if="!readOnly">
                    <Dropdown>
                        <template #trigger>
                            <Button icon="ui/dots" variant="ghost" size="xs" v-bind="$attrs" :aria-label="__('Open dropdown menu')" />
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
        </div>
    </div>
</template>

<script>
import { getActivePinia } from 'pinia';
import InlineEditForm from './InlineEditForm.vue';
import { Button, Dropdown, DropdownMenu, DropdownItem } from '@statamic/cms/ui';
import { containerContextKey } from '@statamic/components/ui/Publish/Container.vue';

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
