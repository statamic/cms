<template>
    <div class="max-w-5xl 3xl:max-w-6xl mx-auto" data-max-width-wrapper>
        <Head :title="__('Edit Fieldset')" />

        <Header :title="__('Edit Fieldset')" icon="fieldsets">
            <ui-command-palette-item
                :category="$commandPalette.category.Actions"
                :text="__('Save')"
                icon="save"
                :action="save"
                prioritize
                v-slot="{ text, url, icon, action }"
            >
                <Button type="submit" variant="primary" @click.prevent="action" v-text="text" />
            </ui-command-palette-item>
        </Header>

        <ui-panel :heading="__('Settings')">
            <ui-card>
                <ui-field :label="__('Title')" :instructions="__('messages.fieldsets_title_instructions')" :errors="errors.title">
                    <ui-input v-model="fieldset.title" />
                </ui-field>
            </ui-card>
        </ui-panel>

        <sections
            class="mt-8"
            tab-id="fieldset"
            :initial-sections="sections"
            :show-section-collapsible-field="true"
            :exclude-fieldset="fieldset.handle"
            with-command-palette
            @updated="sections = $event"
        />
    </div>
</template>

<script>
import Sections from '@/components/blueprints/Sections.vue';
import { Sortable, Plugins } from '@shopify/draggable';
import SuggestsConditionalFields from '@/components/blueprints/SuggestsConditionalFields';
import { Header, Button } from '@/components/ui';
import Head from '@/pages/layout/Head.vue';

export default {
    mixins: [SuggestsConditionalFields],

    components: {
        Head,
        Sections,
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
        sections: {
            get() {
                return this.fieldset.sections;
            },
            set(sections) {
                this.fieldset.sections = sections;
            },
        },

        fieldsForConditionSuggestions() {
            return this.sections.reduce((fields, section) => fields.concat(section.fields || []), []);
        },
    },

    mounted() {
        this.makeSortable();
    },

    watch: {
        fieldset: {
            deep: true,
            handler() {
                this.$dirty.add('fieldsets');
            },
        },
        sections: {
            deep: true,
            handler() {
                this.$nextTick(() => this.makeSortable());
            },
        },
    },

    methods: {
        save() {
            this.$axios[this.method](this.action, this.fieldset)
                .then((response) => {
                    this.$toast.success(__('Saved'));
                    this.errors = {};
                    this.$dirty.remove('fieldsets');
                })
                .catch((e) => {
                    this.$toast.error(e.response.data.message);
                    this.errors = e.response.data.errors;
                });
        },

        makeSortable() {
            if (this.sortableSections) {
                this.sortableSections.destroy();
            }

            if (this.sortableFields) {
                this.sortableFields.destroy();
            }

            this.sortableSections = new Sortable(this.$el.querySelector('.blueprint-sections'), {
                draggable: '.blueprint-section',
                handle: '.blueprint-section-drag-handle',
                mirror: { constrainDimensions: true, appendTo: 'body' },
                plugins: [Plugins.SwapAnimation],
            }).on('sortable:stop', (e) => {
                this.fieldset.sections.splice(e.newIndex, 0, this.fieldset.sections.splice(e.oldIndex, 1)[0]);
            });

            this.sortableFields = new Sortable(this.$el.querySelectorAll('.blueprint-section-draggable-zone'), {
                draggable: '.blueprint-section-field',
                handle: '.blueprint-drag-handle',
                mirror: { constrainDimensions: true, appendTo: 'body' },
                plugins: [Plugins.SwapAnimation],
            }).on('sortable:stop', (e) => {
                const oldSection = this.fieldset.sections.find((section) => section._id === e.oldContainer.dataset.section);
                const newSection = this.fieldset.sections.find((section) => section._id === e.newContainer.dataset.section);

                if (!oldSection || !newSection) {
                    return;
                }

                const field = oldSection.fields.splice(e.oldIndex, 1)[0];
                newSection.fields.splice(e.newIndex, 0, field);
            });
        },
    },

    created() {
        this.$keys.bindGlobal(['mod+s'], (e) => {
            e.preventDefault();
            this.save();
        });

        // Listen for root-form-save events from child components
        // This also happens on the blueprint builder.
        this.$events.$on('root-form-save', () => {
            this.save();
        });
    },

    beforeUnmount() {
        this.$events.$off('root-form-save');

        if (this.sortableSections) {
            this.sortableSections.destroy();
        }

        if (this.sortableFields) {
            this.sortableFields.destroy();
        }
    },
};
</script>
