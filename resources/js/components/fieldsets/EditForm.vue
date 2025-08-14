<template>
    <div>
        <Header :title="__('Edit Fieldset')" icon="fieldsets">
            <Button type="submit" variant="primary" @click.prevent="save" v-text="__('Save')" />
        </Header>

        <ui-panel :heading="__('Settings')">
            <ui-card>
                <ui-field :label="__('Title')" :instructions="__('messages.fieldsets_title_instructions')" :errors="errors.title">
                    <ui-input v-model="fieldset.title" />
                </ui-field>
            </ui-card>
        </ui-panel>

        <ui-panel :heading="__('Fields')">
            <fields
                :fields="fields"
                :editing-field="editingField"
                :exclude-fieldset="fieldset.handle"
                :suggestable-condition-fields="suggestableConditionFields(this)"
                @field-created="fieldCreated"
                @field-updated="fieldUpdated"
                @field-linked="fieldLinked"
                @field-deleted="deleteField"
                @field-editing="editingField = $event"
                @editor-closed="editingField = null"
            />
        </ui-panel>
    </div>
</template>

<script>
import Fields from '../blueprints/Fields.vue';
import { Sortable, Plugins } from '@shopify/draggable';
import SuggestsConditionalFields from '../blueprints/SuggestsConditionalFields';
import { Header, Button } from '@statamic/cms/ui';

export default {
    mixins: [SuggestsConditionalFields],

    components: {
        Fields,
        Header,
        Button,
    },

    props: ['action', 'initialFieldset'],

    data() {
        return {
            method: 'patch',
            initialTitle: this.initialFieldset.title,
            fieldset: clone(this.initialFieldset),
            errors: {},
            editingField: null,
        };
    },

    computed: {
        fields: {
            get() {
                return this.fieldset.fields;
            },
            set(fields) {
                this.fieldset.fields = fields;
            },
        },

        fieldsForConditionSuggestions() {
            return this.fields;
        },
    },

    mounted() {
        this.makeSortable();
    },

    methods: {
        save() {
            this.$axios[this.method](this.action, this.fieldset)
                .then((response) => {
                    this.$toast.success(__('Saved'));
                    this.errors = {};
                })
                .catch((e) => {
                    this.$toast.error(e.response.data.message);
                    this.errors = e.response.data.errors;
                });
        },

        fieldCreated(field) {
            this.fields.push(field);
        },

        fieldUpdated(i, field) {
            this.fields.splice(i, 1, field);
        },

        deleteField(i) {
            this.fields.splice(i, 1);
        },

        fieldLinked(field) {
            this.fields.push(field);
            this.$toast.success(__('Field added'));

            if (field.type === 'reference') {
                this.$nextTick(() => (this.editingField = field._id));
            }
        },

        makeSortable() {
            new Sortable(this.$el.querySelector('.blueprint-section-draggable-zone'), {
                draggable: '.blueprint-section-field',
                handle: '.blueprint-drag-handle',
                mirror: { constrainDimensions: true, appendTo: 'body' },
                plugins: [Plugins.SwapAnimation],
            }).on('sortable:stop', (e) => {
                this.fieldset.fields.splice(e.newIndex, 0, this.fieldset.fields.splice(e.oldIndex, 1)[0]);
            });
        },
    },

    created() {
        this.$keys.bindGlobal(['mod+s'], (e) => {
            e.preventDefault();
            this.save();
        });
    },
};
</script>
