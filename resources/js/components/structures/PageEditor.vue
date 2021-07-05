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
                        <div v-if="validating" class="absolute inset-0 z-10 bg-white bg-opacity-75 flex items-center justify-center">
                            <loading-graphic text="" />
                        </div>

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

                    <div class="text-xs mt-2" v-if="type === 'entry'">
                        <a :href="editEntryUrl" target="_blank" class="flex items-center justify-center text-blue hover:text-blue-dark underline">
                            <svg-icon name="external-link" class="w-4 h-4 mr-1" />
                            {{ __('Edit Entry') }}
                        </a>
                    </div>
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
        editEntryUrl: String
    },

    data() {
        return {
            values: this.initValues(this.initialValues),
            meta: this.initiMeta(this.initialMeta),
            error: null,
            errors: {},
            validating: false
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
            this.validating = true;

            let title = this.values.title;
            let url = this.values.url;

            const postUrl = cp_url('navigation/links/pages'); // todo: get url properly

            this.$axios.post(postUrl, {
                type: this.type,
                values: this.values
            }).then(response => {
                this.$emit('submitted', { title, url, values: _.omit(this.values, ['title', 'url']) });
            }).catch(e => {
                this.validating = false;
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                    this.$toast.error(message);
                } else if (e.response) {
                    this.$toast.error(e.response.data.message);
                } else {
                    this.$toast.error(e || 'Something went wrong');
                }
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
