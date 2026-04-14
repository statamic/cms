<template>
    <div class="blueprint-section-field field-grid-item pr-1.5 w-full bg-white dark:bg-gray-850 rounded-xl ring ring-gray-200 dark:ring-x-0 dark:ring-b-0 dark:ring-gray-700 blueprint-section-field-w-full">
        <div class="pr-1.5 w-full bg-white dark:bg-gray-850 rounded-xl ring ring-gray-200 dark:ring-x-0 dark:ring-b-0 dark:ring-gray-700">
            <ui-card class="py-0.75! px-2! z-10 relative blueprint-section/import w-full">
                <div class="flex items-center gap-2">
                    <ui-icon name="handles" class="blueprint-drag-handle size-4 cursor-grab text-gray-300 dark:text-gray-600" />
                    <div class="flex flex-1 items-center justify-between">
                        <div class="flex flex-1 items-center py-2">
                            <ui-icon class="size-4 me-2 text-ui-accent-text/80" name="fieldsets" />
                            <div class="flex items-center gap-2">
                                <a class="cursor-pointer overflow-hidden text-ellipsis text-sm text-ui-accent-text hover:text-ui-accent-text/80" :href="fieldsetEditUrl" v-text="fieldsetTitle" v-tooltip="__('Edit Fieldset')" />
                                <ui-icon name="link" class="text-gray-400" />
                                <span class="text-gray-500 font-mono text-2xs" v-text="__('Fieldset')" />
                                <ui-badge v-if="sectionBadgeText" size="sm" color="gray" :text="sectionBadgeText" />
                                <ui-badge
                                    v-if="field.prefix"
                                    size="sm"
                                    color="gray"
                                    :text="`${__('Prefix')}: ${field.prefix}`"
                                />
                            </div>
                        </div>
                        <div class="flex items-center">
                            <ui-button size="sm" icon="cog" variant="subtle" inset @click.prevent="$emit('edit')" v-tooltip="__('Configure import')" />
                            <ui-button size="sm" icon="trash" variant="subtle" inset @click.prevent="$emit('deleted')" v-tooltip="__('Remove')" />
                            <ui-stack :open="isEditing" @update:open="editorClosed" inset :show-close-button="false" :wrap-slot="false">
                                <field-settings
                                    ref="settings"
                                    :id="field._id"
                                    :root="isRoot"
                                    :fields="fields"
                                    :config="fieldConfig"
                                    :is-inside-set="isInsideSet"
                                    @committed="settingsUpdated"
                                    @closed="editorClosed"
                                />
                            </ui-stack>
                        </div>
                    </div>
                </div>
            </ui-card>
        </div>
    </div>
</template>

<script>
import Field from './Field.vue';
import FieldSettings from '../fields/ImportSettings.vue';

export default {
    mixins: [Field],

    components: { FieldSettings },

    inject: {
        isInsideSet: { default: false },
    },

    computed: {
        fieldsetTitle() {
            const title = this.$page?.props?.fieldsets?.[this.field.fieldset]?.title;

            return title ? __(title) : this.field.fieldset;
        },

        fieldsetHasSections() {
            return this.$page?.props?.fieldsets?.[this.field.fieldset]?.has_sections === true;
        },

        fieldsetSectionsCount() {
            return this.$page?.props?.fieldsets?.[this.field.fieldset]?.sections_count ?? 0;
        },

        fieldsetEditUrl() {
            return cp_url(`fields/fieldsets/${this.field.fieldset}/edit`);
        },

        sectionBehavior() {
            return this.field.section_behavior ?? 'preserve';
        },

        sectionBadgeText() {
            if (!this.fieldsetHasSections) {
                return null;
            }

            if (this.sectionBehavior === 'flatten') {
                return __n('Ignoring Section|Ignoring Sections', this.fieldsetSectionsCount);
            }

            return __n('Has Section|Has Sections', this.fieldsetSectionsCount);
        },

        fieldConfig() {
            const { _id, type, ...config } = this.field;
            return config;
        },
    },

    methods: {
        settingsUpdated(settings) {
            const field = Object.assign({}, this.field, settings);
            this.$emit('updated', field);
        },

        editorClosed() {
            this.$emit('editor-closed');
        },
    },
};
</script>
