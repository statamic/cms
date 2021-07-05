<template>

    <stack narrow name="page-tree-linker" @closed="$emit('closed')">
        <div slot-scope="{ close }" class="bg-white h-full flex flex-col">

            <div class="bg-grey-20 px-3 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between">
                {{ __('Link') }}
                <button
                    type="button"
                    class="btn-close"
                    @click="close"
                    v-html="'&times'" />
            </div>

            <div class="flex-1 overflow-auto">

                <publish-container
                    v-if="adjustedBlueprint"
                    ref="container"
                    name="nav-page-editor"
                    :blueprint="adjustedBlueprint"
                    :values="values"
                    :meta="meta"
                    :errors="errors"
                    @updated="values = $event"
                >
                    <div slot-scope="{ container, setFieldValue, setFieldMeta }">
                        <publish-fields
                            :fields="fields"
                            @updated="setFieldValue"
                            @meta-updated="setFieldMeta"
                            @focus="container.$emit('focus', $event)"
                            @blur="container.$emit('blur', $event)"
                        />
                    </div>
                </publish-container>

                <div class="p-3">
                    <button @click="submit" class="btn-primary w-full">{{ __('Submit') }}</button>
                </div>

            </div>

        </div>
    </stack>

</template>

<script>
export default {

    props: {
        type: String,
        initialTitle: String,
        initialUrl: String,
        initialValues: Object,
        initialMeta: Object,
        blueprint: Object,
    },

    data() {
        return {
            values: this.initValues(this.initialValues),
            meta: this.initiMeta(this.initialMeta),
            errors: {}
        }
    },

    computed: {
        adjustedBlueprint() {
            let blueprint = clone(this.blueprint);

            // todo only add the fields if they're not already in the blueprint.

            if (this.type == 'url') {
                blueprint.sections[0].fields.unshift({
                    handle: 'url',
                    type: 'text',
                    display: __('URL'),
                    instructions: __('Enter any internal or external URL. Leave blank for a text-only item.'),
                });
            }

            blueprint.sections[0].fields.unshift({
                handle: 'title',
                type: 'text',
                display: __('Title'),
                instructions: __('Link display text. Leave blank to use the URL.'),
            });

            return blueprint;
        },

        fields() {
            return _.chain(this.adjustedBlueprint.sections)
                .map(section => section.fields)
                .flatten(true)
                .value();
        }
    },

    methods: {
        submit() {
            let title = this.values.title;
            let url = this.values.url;

            // todo: actual blueprint validation. submit to server side.
            if (!title && !url) {
                alert('You need at least a title or URL.');
                return;
            }

            this.$emit('submitted', {
                title,
                url,
                values: _.omit(this.values, ['title', 'url']),
            });
        },

        initValues(values) {
            return {
                ...values,
                title: this.initialTitle,
                url: this.initialUrl
            };
        },

        initiMeta(meta) {
            return {...meta, title: null, url: null};
        }
    },

    created() {
        this.$keys.bindGlobal('enter', this.submit)
    }

}
</script>
