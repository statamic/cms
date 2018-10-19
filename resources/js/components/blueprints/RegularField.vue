<template>

    <div class="blueprint-section-field">
        <div class="blueprint-section-field-inner">
            <div class="blueprint-drag-handle w-4 border-r"></div>
            <div class="flex flex-1 items-center justify-between">
                <div class="flex-1 px-2 py-1">
                    <span class="font-medium mr-1">{{ field.config.display || field.handle }}</span>
                    <span class="font-mono text-2xs text-grey-light">{{ field.handle }}</span>
                </div>
                <div class="pr-1">
                    <button @click.prevent="$emit('edit')" class="opacity-50 hover:opacity-100"><span class="icon icon-cog" /></button>
                    <button @click.prevent="$emit('deleted')" class="opacity-50 hover:opacity-100"><span class="icon icon-cross" /></button>

                    <portal to="modals" v-if="isEditing">
                        <modal name="field-settings" width="90%" height="90%" @closed="$emit('editor-closed')">
                            <field-settings
                                ref="settings"
                                type="textarea"
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
import FieldSettings from '../fields/Settings.vue';

export default {

    mixins: [Field],

    components: { FieldSettings },

    computed: {

        fieldConfig() {
            return Object.assign({}, this.field.config, {
                handle: this.field.handle
            });
        }

    },

    methods: {

        configUpdated(handle, value) {
            if (handle === 'handle') {
                this.field.handle = value;
            } else {
                this.field.config[handle] = value;

                if (this.field.type === 'reference' && this.field.config_overrides.indexOf(handle) === -1) {
                    this.field.config_overrides.push(handle);
                }
            }

            this.$emit('updated', this.field);
        }

    }

}
</script>
