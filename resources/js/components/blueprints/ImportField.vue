<template>

    <div class="blueprint-section-field blueprint-section-import blueprint-section-field-w-full">
        <div class="blueprint-section-field-inner">
            <div class="blueprint-drag-handle w-4 border-r"></div>
            <div class="flex flex-1 items-center justify-between">
                <div class="flex-1 px-2 py-1">
                    <span class="font-medium mr-1">Import</span>
                    <span class="font-mono text-2xs text-grey-light">{{ field.fieldset }}</span>
                </div>
                <div class="pr-1">
                    <button @click.prevent="$emit('edit')" class="opacity-50 hover:opacity-100"><span class="icon icon-cog" /></button>
                    <button @click.prevent="$emit('deleted')" class="opacity-50 hover:opacity-100"><span class="icon icon-cross" /></button>

                    <portal to="modals" v-if="isEditing">
                        <modal name="field-settings" width="90%" height="90%" @closed="$emit('editor-closed')">
                            <field-settings
                                ref="settings"
                                :root="isRoot"
                                :config="fieldConfig"
                                @updated="configUpdated"
                            />
                        </modal>
                    </portal>
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
        }

    }

}
</script>
