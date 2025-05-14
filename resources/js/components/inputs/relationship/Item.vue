<template>
    <div class="w-full bg-white px-1.5 py-2 gap-2 relative text-base rounded-lg flex items-center z-2 dark:bg-gray-900 border border-gray-200 dark:border-x-0 dark:border-t-0 dark:border-white/15 dark:inset-shadow-2xs dark:inset-shadow-black shadow-ui-sm" :class="{ invalid: item.invalid }">
        <ui-icon name="handles" class="item-move cursor-grab sortable-handle size-4 text-gray-400" v-if="sortable" />
        <div class="flex items-center flex-1">
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
                    class="hidden whitespace-nowrap text-4xs uppercase text-gray-600 @sm:block me-2"
                />

                <div class="flex items-center" v-if="!readOnly">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" @click="edit" v-if="editable" />
                        <dropdown-item :text="__('Unlink')" class="warning" @click="$emit('removed')" />
                    </dropdown-list>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { getActivePinia } from 'pinia';
import InlineEditForm from './InlineEditForm.vue';

export default {
    components: {
        InlineEditForm,
    },

    inject: {
        storeName: {
            default: null,
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
                const storeRefs = getActivePinia()
                    ._s.values()
                    .map((store) => store.reference);
                if (Array.from(storeRefs).includes(this.item.reference)) {
                    this.$toast.error(__("You're already editing this item."));
                    return;
                }
            }

            this.isEditing = true;
        },

        itemUpdated(responseData) {
            this.item.title = responseData.title;
            this.item.published = responseData.published;
            this.item.private = responseData.private;
            this.item.status = responseData.status;

            this.$events.$emit(`live-preview.${this.storeName}.refresh`);
        },
    },
};
</script>
