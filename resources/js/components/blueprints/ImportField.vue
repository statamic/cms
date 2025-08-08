<template>
    <div class="blueprint-section-field field-grid-item pr-1.5 w-full bg-white dark:bg-gray-850 rounded-xl ring ring-gray-200 dark:ring-x-0 dark:ring-b-0 dark:ring-gray-700 blueprint-section-field-w-full">
        <div class="pr-1.5 w-full bg-white dark:bg-gray-850 rounded-xl ring ring-gray-200 dark:ring-x-0 dark:ring-b-0 dark:ring-gray-700">
            <ui-card class="py-0.75! px-2! z-10 relative blueprint-section/import w-full">
                <div class="flex items-center gap-2">
                    <ui-icon name="handles" class="blueprint-drag-handle size-4 cursor-grab text-gray-300" />
                    <div class="flex flex-1 items-center justify-between">
                        <div class="flex flex-1 items-center py-2">
                            <ui-icon class="size-4 me-2 text-blue-600" name="fieldsets" />
                            <div class="flex items-center gap-2">
                            <!-- @TODO: Show fieldset.title -->
                                <button class="cursor-pointer overflow-hidden text-ellipsis text-sm text-blue-600 hover:text-blue-500" v-text="field.fieldset" @click="$emit('edit')" />
                                <ui-icon name="link" class="text-gray-400" />
                                <span class="text-gray-500 font-mono text-2xs" v-text="__('fieldset')" />
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <ui-button size="sm" icon="trash" variant="subtle" @click.prevent="$emit('deleted')" v-tooltip="__('Remove')" />
                            <stack name="field-settings" v-if="isEditing" @closed="editorClosed">
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
                            </stack>
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
