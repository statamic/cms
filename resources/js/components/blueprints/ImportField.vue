<template>
    <div
        class="blueprint-section-field field-grid-item dark:bg-gray-850 dark:ring-x-0 dark:ring-b-0 blueprint-section-field-w-full w-full rounded-xl bg-white pr-1.5 ring ring-gray-200 dark:ring-gray-700"
    >
        <div
            class="dark:bg-gray-850 dark:ring-x-0 dark:ring-b-0 w-full rounded-xl bg-white pr-1.5 ring ring-gray-200 dark:ring-gray-700"
        >
            <ui-card class="blueprint-section/import relative z-10 w-full px-2! py-0.75!">
                <div class="flex items-center gap-2">
                    <ui-icon name="handles" class="blueprint-drag-handle size-4 cursor-grab text-gray-300" />
                    <div class="flex flex-1 items-center justify-between">
                        <div class="flex flex-1 items-center py-2">
                            <ui-icon class="me-2 size-4 text-blue-600" name="fieldsets" />
                            <div class="flex items-center gap-2">
                                <!-- @TODO: Show fieldset.title -->
                                <button
                                    class="cursor-pointer overflow-hidden text-sm text-ellipsis text-blue-600 hover:text-blue-500"
                                    v-text="field.fieldset"
                                    @click="$emit('edit')"
                                />
                                <ui-icon name="link" class="text-gray-400" />
                                <span class="text-2xs font-mono text-gray-500" v-text="__('fieldset')" />
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <ui-button
                                size="sm"
                                icon="trash"
                                variant="subtle"
                                @click.prevent="$emit('deleted')"
                                v-tooltip="__('Remove')"
                            />
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
