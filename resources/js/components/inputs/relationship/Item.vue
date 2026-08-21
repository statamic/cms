<template>
    <div
        class="shadow-ui-sm relative z-(--z-index-above) flex w-full h-full items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 [&:has(.cursor-grab)]:px-1.5 py-1.5 mb-1.5 last:mb-0 text-base dark:border-gray-700 dark:with-contrast:border-gray-500 dark:bg-gray-900"
    >
        <ui-icon name="handles" class="item-move sortable-handle size-4 cursor-grab text-gray-300 dark:text-gray-700" v-if="sortable" />
        <div class="flex flex-1 items-center line-clamp-1 text-sm text-gray-600 dark:text-gray-300">
            <ui-status-indicator v-if="item.status" :status="item.status" class="me-2" />

            <div
                v-if="item.invalid"
                v-tooltip.top="__('messages.relationship_item_unavailable')"
                class="line-clamp-1 text-sm text-gray-500 dark:text-gray-400"
            >
                <ItemLabel :item="item" :group-label="groupLabel" />
            </div>

            <a
                v-if="!item.invalid && editable"
                @click.prevent="edit"
                class="line-clamp-1 text-sm text-gray-600 dark:text-gray-300"
                v-tooltip="itemTitle"
                :href="item.edit_url"
            >
                <ItemLabel :item="item" :group-label="groupLabel" />
            </a>

            <div v-if="!item.invalid && !editable" class="line-clamp-1">
                <ItemLabel :item="item" :group-label="groupLabel" />
            </div>

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
        </div>
    </div>
</template>

<script>
import { getActivePinia } from 'pinia';
import InlineEditForm from './InlineEditForm.vue';
import ItemLabel from './ItemLabel.vue';
import { Button, Dropdown, DropdownMenu, DropdownItem, publishContextKey as containerContextKey } from '@/components/ui';
import { selectedSiteGroupLabel } from '@/util/site-groups.js';

export default {
    components: {
        Button,
        DropdownItem,
        DropdownMenu,
        Dropdown,
        InlineEditForm,
        ItemLabel,
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

    computed: {
        itemTitle() {
            return __(this.item.title);
        },

        sitesHaveNamedGroups() {
            if (this.config?.type !== 'sites') {
                return false;
            }

            const sites = Statamic.$config.get('sites') || [];

            return sites.some((site) => site.group);
        },

        groupLabel() {
            if (this.config?.type !== 'sites') {
                return null;
            }

            return selectedSiteGroupLabel(this.itemWithSiteGroup, this.sitesHaveNamedGroups);
        },

        itemWithSiteGroup() {
            if (this.item.group || this.item.group_handle) {
                return this.item;
            }

            const site = (Statamic.$config.get('sites') || []).find((site) => site.handle === this.item.id);

            if (!site) {
                return this.item;
            }

            return {
                ...this.item,
                group: site.group,
                group_handle: site.group_handle,
            };
        },
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
