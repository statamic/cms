<template>

    <div>
        <popper ref="popper" trigger="click" :append-to-body="true" :options="{ placement: 'right' }">
            <div class="popover w-96">

                <div>
                    <p class="text-sm font-medium mb-1">Select a field from an existing fieldset:</p>
                    <suggest-fieldtype
                        :config="{max_items: 1, placeholder: ''}"
                        name="field"
                        :suggestions-prop="suggestions"
                        :value="[fieldReference]"
                        @updated="addReferenceField($event[0])"
                    />
                </div>

                <div class="border-grey-lighter border-t mt-2 pt-2 text-grey-light text-xs">
                    <div class="mb-1">More options:</div>
                    <ul class="pl-2">
                        <li><button class="text-blue" @click="addInlineField">Create a one-time field</button></li>
                        <li><button class="text-blue" @click.prevent="">Create a new reusable field</button></li>
                        <li><button class="text-blue" @click.prevent="">Import a fieldset</button></li>
                    </ul>
                </div>

                <portal to="modals" v-if="selectingFieldtype">
                    <modal name="fieldtype-selector" width="90%" height="90%">
                        <fieldtype-selector @selected="fieldtypeSelected" />
                    </modal>
                </portal>

            </div>
            <button
                slot="reference"
                class="btn btn-default btn-small"
                v-text="`+ ${__('Add Field')}`"
            />
        </popper>

    </div>

</template>

<script>
import uniqid from 'uniqid';
import Popper from 'vue-popperjs';
import FieldtypeSelector from '../fields/FieldtypeSelector.vue';

export default {

    components: {
        Popper,
        FieldtypeSelector
    },

    data() {
        return {
            fieldReference: null,
            selectingFieldtype: false,
            suggestions: Object.values(window.Statamic.fieldsetFields).map(field => {
                return {
                    value: `${field.fieldset.handle}.${field.handle}`,
                    text: field.display,
                    optgroup: field.fieldset.title
                };
            })
        }
    },

    computed: {

        isAddingReferenceField() {
            return this.type === 'reference';
        }

    },

    methods: {

        addReferenceField(reference) {
            if (!reference) return;

            this.fieldReference = reference;

            const field = JSON.parse(JSON.stringify(window.Statamic.fieldsetFields[reference]));

            this.$emit('added', {
                _id: uniqid(),
                type: 'reference',
                field_reference: reference,
                handle: field.handle,
                config: field.config,
                config_overrides: []
            });

            this.fieldReference = null;
            this.$refs.popper.doClose();
        },

        addInlineField() {
            this.selectingFieldtype = true;
            this.$nextTick(() => this.$modal.show('fieldtype-selector'));
        },

        fieldtypeSelected(field) {
            this.$emit('added', {
                _id: uniqid(),
                type: 'inline',
                handle: field.type,
                config: {
                    ...field,
                    display: field.type,
                },
            });
        },

        addImportField() {

        }

    }

}
</script>
