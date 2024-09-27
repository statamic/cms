<template>

    <div class="blueprint-section-field blueprint-section-import blueprint-section-field-w-full">
        <div class="blueprint-section-field-inner">
            <div class="blueprint-drag-handle w-4 ltr:border-r rtl:border-l dark:border-dark-300"></div>
            <div class="flex flex-1 items-center justify-between">
                <div class="flex items-center flex-1 rtl:pl-4 ltr:pr-4 py-2 rtl:pr-2 ltr:pl-2">
                    <svg-icon class="flex-none text-gray-700 dark:text-dark-150 h-4 w-4 rtl:ml-2 ltr:mr-2" name="paperclip" v-tooltip="__('Linked fieldset')" />
                    <a class="break-all" @click="$emit('edit')">
                        <span v-text="__('Fieldset')" />
                        <span class="font-mono text-3xs text-gray-600 dark:text-dark-175 rtl:mr-2 ltr:ml-2">{{ field.fieldset }}</span>
                    </a>
                </div>
                <div class="flex-none rtl:pl-2 ltr:pr-2 flex">
                    <button @click.prevent="$emit('deleted')" class="text-gray-600 dark:text-dark-150 hover:text-gray-950 dark:hover:text-dark-100"><svg-icon name="micro/trash" class="w-4 h-4" /></button>
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
            return _.omit(this.field, ['_id', 'type']);
        }

    },

    methods: {

        settingsUpdated(settings) {
            const field = Object.assign({}, this.field, settings);
            this.$emit('updated', field);
        },

        editorClosed() {
            this.$emit('editor-closed');
        }

    }

}
</script>
