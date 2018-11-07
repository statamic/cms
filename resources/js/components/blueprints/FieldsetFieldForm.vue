<template>

    <div class="flex flex-col h-full">

        <div class="flex items-center p-3 bg-grey-lightest border-b text-center">
            <span>Create a new field</span>
        </div>

        <div class="flex-1 overflow-scroll">

            <div class="publish-fields">

                <div class="form-group">
                    <label class="block">{{ __('Fieldset') }}</label>
                    <div class="help-block">{{ __('Select the fieldset this field should belong to. You may create a new fieldset.') }}</div>

                    <fieldset-fieldtype
                        name="fieldset"
                        :value="fieldset"
                        :config="{}"
                        @updated="fieldset = $event"
                    />

                </div>

            </div>

            <fieldtype-selector
                v-if="!field"
                @selected="fieldtypeSelected"
            />

            <field-settings
                v-if="field"
                ref="settings"
                :type="field.type"
                v-model="field"
            />

            <button class="btn btn-primary" @click="submit">Submit</button>

        </div>

    </div>

</template>

<script>
import axios from 'axios';
import FieldtypeSelector from '../fields/FieldtypeSelector.vue';
import FieldSettings from '../fields/Settings.vue';

export default {

    components: {
        FieldtypeSelector,
        FieldSettings
    },

    data() {
        return {
            fieldset: null,
            field: null,
        }
    },

    methods: {

        fieldtypeSelected(field) {
            this.field = {
                ...field,
                handle: 'new_field',
                display: 'New Field'
            };
        },

        submit() {
            if (!this.fieldset || !this.field) {
                this.$notify.error('Cannot create without a fieldset and field.');
                return;
            }

            const url = cp_url(`fieldsets/${this.fieldset}/fields`);

            axios.post(url, this.field).then(response => {
                const reference = `${this.fieldset}/${this.field.handle}`;
                window.Statamic.fieldsetFields[reference] = response.data;
                this.$emit('created', reference);
                this.$notify.success('Field created');
            });
        }

    }

}
</script>
