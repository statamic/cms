<template>

    <div class="blueprint-section-field blueprint-section-import blueprint-section-field-w-full">
        <div class="blueprint-section-field-inner">
            <div class="blueprint-drag-handle w-4 border-r"></div>
            <div class="flex flex-1 items-center justify-between">
                <div class="flex-1 px-2 pl-1 py-1">
                    <span class="icon icon-flow-tree text-grey-40 mr-1"></span>
                    <span class="font-medium mr-1">Import</span>
                    <span class="font-mono text-2xs text-grey-40">{{ field.fieldset }}</span>
                </div>
                <div class="pr-1">
                    <button @click.prevent="$emit('edit')" class="opacity-50 hover:opacity-100"><span class="icon icon-cog" /></button>
                    <button @click.prevent="$emit('deleted')" class="opacity-50 hover:opacity-100"><span class="icon icon-cross" /></button>

                    <stack name="field-settings" v-if="isEditing" @closed="editorClosed">
                        <field-settings
                            ref="settings"
                            :root="isRoot"
                            :config="fieldConfig"
                            @updated="configUpdated"
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

        configUpdated(handle, value) {
            this.field[handle] = value;

            this.$emit('updated', this.field);
        },

        editorClosed() {
            this.$emit('editor-closed');
        }

    }

}
</script>
