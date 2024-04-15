<template>

    <div>

        <header class="mb-6">
            <breadcrumb :url="breadcrumbUrl" :title="__('Fieldsets')" />
            <div class="flex items-center justify-between">
                <h1>{{ __(initialTitle) }}</h1>
                <button type="submit" class="btn-primary" @click.prevent="save" v-text="__('Save')" />
            </div>
        </header>

        <div class="publish-form card p-0 @container mb-8">
            <div class="publish-fields">
                <div class="form-group w-full">
                    <div class="field-inner">
                        <label class="block">{{ __('Title') }}</label>
                        <small class="help-block -mt-2">{{ __('messages.fieldsets_title_instructions') }}</small>
                        <div v-if="errors.title">
                            <small class="help-block text-red-500" v-for="(error, i) in errors.title" :key="i" v-text="error" />
                        </div>
                    </div>
                    <div>
                        <input type="text" name="title" class="input-text" v-model="fieldset.title" autofocus="autofocus">
                    </div>
                </div>
            </div>

        </div>

        <div class="content mt-10 mb-4">
            <h2 v-text="__('Fields')" />
        </div>

        <div class="card @container" :class="{ 'pt-2': !fields.length }">
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
        </div>

    </div>

</template>

<script>
import Fields from '../blueprints/Fields.vue';
import {Sortable, Plugins} from '@shopify/draggable';
import SuggestsConditionalFields from '../blueprints/SuggestsConditionalFields';

export default {

    mixins: [SuggestsConditionalFields],

    components: {
        Fields
    },

    props: ['action', 'initialFieldset', 'breadcrumbUrl'],

    data() {
        return {
            method: 'patch',
            initialTitle: this.initialFieldset.title,
            fieldset: clone(this.initialFieldset),
            errors: {},
            editingField: null,
        }
    },

    computed: {

        fields: {
            get() {
                return this.fieldset.fields;
            },
            set(fields) {
                this.fieldset.fields = fields;
            }
        },

        fieldsForConditionSuggestions() {
            return this.fields;
        }

    },

    mounted() {
        this.makeSortable();
    },

    methods: {

        save() {
            this.$axios[this.method](this.action, this.fieldset)
                .then(response => {
                    this.$toast.success(__('Saved'));
                    this.errors = {};
                })
                .catch(e => {
                    this.$toast.error(e.response.data.message);
                    this.errors = e.response.data.errors;
                })
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
                this.$nextTick(() => this.editingField = field._id);
            }
        },

        makeSortable() {
            new Sortable(this.$el.querySelector('.blueprint-section-draggable-zone'), {
                draggable: '.blueprint-section-field',
                handle: '.blueprint-drag-handle',
                mirror: { constrainDimensions: true, appendTo: 'body' },
                plugins: [Plugins.SwapAnimation]
            }).on('sortable:stop', e => {
                this.fieldset.fields.splice(e.newIndex, 0, this.fieldset.fields.splice(e.oldIndex, 1)[0]);
            });
        }
    },

    created() {
        this.$keys.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            this.save();
        });
    },

}
</script>
