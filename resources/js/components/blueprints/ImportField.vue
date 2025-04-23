<template>
    <div class="blueprint-section-field blueprint-section-import blueprint-section-field-w-full">
        <div class="blueprint-section-field-inner">
            <div class="blueprint-drag-handle w-4 dark:border-dark-300 ltr:border-r rtl:border-l"></div>
            <div class="flex flex-1 items-center justify-between">
                <div class="flex flex-1 items-center py-2 ltr:pl-2 ltr:pr-4 rtl:pl-4 rtl:pr-2">
                    <svg-icon
                        class="h-4 w-4 flex-none text-gray-700 dark:text-dark-150 ltr:mr-2 rtl:ml-2"
                        name="paperclip"
                        v-tooltip="__('Linked fieldset')"
                    />
                    <a class="break-all" @click="$emit('edit')">
                        <span v-text="__('Fieldset')" />
                        <span class="font-mono text-3xs text-gray-600 dark:text-dark-175 ltr:ml-2 rtl:mr-2">{{
                            field.fieldset
                        }}</span>
                    </a>
                </div>
                <div class="flex flex-none ltr:pr-2 rtl:pl-2">
                    <button
                        @click.prevent="$emit('deleted')"
                        class="text-gray-600 hover:text-gray-950 dark:text-dark-150 dark:hover:text-dark-100"
                    >
                        <svg-icon name="micro/trash" class="h-4 w-4" />
                    </button>
                    <stack name="field-settings" v-if="isEditing" @closed="editorClosed">
                        <field-settings
                            ref="settings"
                            :id="field._id"
                            :root="isRoot"
                            :fields="fields"
                            :config="fieldConfig"
                            @committed="settingsUpdated"
                            @closed="editorClosed"
                        />
                    </stack>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Field from './Field.vue';
import FieldSettings from '../fields/ImportSettings.vue';

export default {
    mixins: [Field],

    components: { FieldSettings },

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
