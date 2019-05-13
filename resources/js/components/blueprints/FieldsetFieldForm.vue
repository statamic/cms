<template>

    <div class="h-full overflow-auto p-4 bg-grey-30 h-full">

        <div class="flex items-center mb-3 -mt-1">
            <div class="flex-1">
                <h1> Create a new field </h1>
                <p class="block text-xs text-grey-40 font-medium leading-none mt-1 flex items-center">
                    You can create a field here that'll be added to a fieldset so you can reuse it later.
                </p>
            </div>
            <button
                class="btn btn-primary"
                @click.prevent="submit"
                v-text="__('Submit')"
            ></button>
        </div>

        <div class="card">

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

                <div class="form-group" v-if="field">
                    <label class="block">Fieldtype</label>
                    <div class="input-text flex justify-between">
                        <div class="flex items-center">
                            <svg-icon class="h-4 w-4 mr-1 inline-block text-grey-40 text-current" :name="fieldtype.icon"></svg-icon>
                            {{ fieldtype.title }}
                        </div>
                        <div class="text-xs text-grey">
                            <button @click="field = null" class="text-blue">Choose different fieldtype</button>
                            or
                            <button @click="editingFieldSettings = true" class="text-blue">configure field</button>
                        </div>
                    </div>
                </div>

            </div>

            <fieldtype-selector
                v-if="!field"
                @selected="fieldtypeSelected"
            />

            <stack name="field-settings" v-if="editingFieldSettings" @closed="editingFieldSettings = false">
                <field-settings
                    ref="settings"
                    :type="field.type"
                    v-model="field"
                    @closed="editingFieldSettings = false"
                />
            </stack>

        </div>

    </div>

</template>

<script>
import ProvidesFieldtypes from '../fields/ProvidesFieldtypes';
import FieldtypeSelector from '../fields/FieldtypeSelector.vue';
import FieldSettings from '../fields/Settings.vue';

export default {

    mixins: [ProvidesFieldtypes],

    components: {
        FieldtypeSelector,
        FieldSettings
    },

    data() {
        return {
            fieldset: null,
            field: null,
            editingFieldSettings: false
        }
    },

    computed: {

        fieldtype() {
            return _.findWhere(this.fieldtypes, { handle: this.field.type });
        }

    },

    methods: {

        fieldtypeSelected(field) {
            this.field = {
                ...field,
                handle: 'new_field',
                display: 'New Field'
            };
            this.editingFieldSettings = true;
        },

        submit() {
            if (!this.fieldset || !this.field) {
                this.$notify.error('Cannot create without a fieldset and field.');
                return;
            }

            const url = cp_url(`fieldsets/${this.fieldset}/fields`);

            this.$axios.post(url, this.field).then(response => {
                const reference = `${this.fieldset}/${this.field.handle}`;
                window.Statamic.$config.get('fieldsetFields')[reference] = response.data;
                this.$emit('created', reference);
                this.$notify.success('Field created');
            });
        }

    }

}
</script>
