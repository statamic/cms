<template>

    <div class="blueprint-section-field blueprint-section-import blueprint-section-field-w-full">
        <div class="blueprint-section-field-inner">
            <div class="blueprint-drag-handle w-4 border-r"></div>
            <div class="flex flex-1 items-center justify-between">
                <div class="flex items-center flex-1 pr-2 py-1 pl-1">
                    <svg-icon class="flex-none text-grey-70 h-4 w-4 mr-1" name="paperclip" v-tooltip="__('Linked fieldset')" />
                    <a class="break-all" @click="$emit('edit')">
                        <span v-text="__('Fieldset')" />
                        <span class="font-mono text-3xs text-grey-60">{{ field.fieldset }}</span>
                    </a>
                </div>
                <div class="flex-none pr-1 flex">
                    <button @click.prevent="$emit('deleted')" class="text-grey-60 hover:text-grey-100"><svg-icon name="trash" class="w-4 h-4" /></button>
                    <stack name="field-settings" v-if="isEditing" @closed="editorClosed">
                        <field-settings
                            ref="settings"
                            :root="isRoot"
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
