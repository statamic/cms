<template>
    <div>
        <div class="sticky flex items-center mb-3 w-full">
            <h1 class="flex-1" v-if="create">
                {{ __('Create Formset') }}
            </h1>

            <h1 class="flex-1" v-if="!create">
                {{ __('Editing Formset') }}:
                <strong>{{ formsetTitle }}</strong>
            </h1>

            <button type="button" class="btn btn-primary" v-on:click="save()">{{ __('Save') }}</button>
        </div>

        <div class="px-3">
            <div class="card p-0">
                <div class="fieldset-builder">

                    <div class="form-group">
                        <label class="block">{{ __('Title') }}</label>
                        <small class="help-block">{{ __('cp.formset_title_instructions') }}</small>
                        <input type="text" class="input-text" v-model="formset.title" autofocus="autofocus" />
                    </div>

                    <div class="form-group" v-if="create">
                        <label class="block">{{ __('Slug') }}</label>
                        <small class="help-block">{{ __('cp.formset_slug_instructions') }}</small>
                        <input type="text" class="input-text" v-model="slug" />
                    </div>

                    <div class="form-group">
                        <label class="block">{{ __('Metrics') }}</label>
                        <small class="help-block">{{ __('cp.formset_metrics_instructions') }}</small>
                        <grid-fieldtype :value="formset.metrics" :config="metricsGridConfig" name="metrics" @input="formset.metrics = $event"></grid-fieldtype>
                    </div>

                    <div class="form-group">
                        <label class="block">{{ __('Emails') }}</label>
                        <small class="help-block">{{ __('cp.formset_emails_instructions') }}</small>
                        <grid-fieldtype :value="formset.email" :config="emailGridConfig" name="email" @input="formset.email = $event"></grid-fieldtype>
                    </div>

                    <div class="form-group">
                        <label class="block">{{ __('Honeypot Field') }}</label>
                        <small class="help-block">{{ __('cp.formset_honeypot_instructions') }} <a href="https://docs.statamic.com/forms#honeypot">{{ __('What\'s a honeypot?') }}</a></small>
                        <input type="text" class="input-text" v-model="formset.honeypot" />
                    </div>

                    <div class="form-group">
                        <label class="block">{{ __('Store Submissions') }}</label>
                        <small class="help-block">{{ __('cp.formset_store_instructions') }}</small>
                        <toggle-fieldtype :value="formset.store" :config="{}" name="store"></toggle-fieldtype>
                    </div>

                </div>
            </div>

            <div class="card p-3">
                <div class="head clearfix">
                    <h2 class="m-0">{{ __('Fields') }}</h2>
                    <small class="help-block">{{ __('cp.formset_fields_instructions') }}</small>
                </div>

                <formset-fields-builder v-model="formset.fields"></formset-fields-builder>
            </div>
        </div>

    </div>
</template>

<script>
export default {

    components: {
        'formset-fields-builder': require('./Fields.vue'),
        'formset-columns': require('./columns')
    },

    props: {
        initialFormset: Object,
        'formsetTitle': String,
        'formsetName': String,
        'create': {
            type: Boolean,
            default: false
        },
        saveMethod: String,
        'saveUrl': String
    },

    data: function () {
        return {
            slug: null,
            formset: this.initialFormset
        }
    },

    computed: {
        columns: {
            get: function() {
                return this.formset.columns || [];
            },
            set: function(columns) {
                this.formset.columns = columns;
            }
        },

        metricsGridConfig: function() {
            return {
                add_row: __('Metric'),
                mode: 'stacked',
                fields: [
                    {
                        handle: 'type',
                        display: __('Metric Type'),
                        width: 25,
                        type: 'select',
                        options: [
                            { value: 'sum', text: __('Sum') },
                            { value: 'total', text: __('Total') },
                            { value: 'average', text: __('Average') }
                        ]
                    },
                    {
                        handle: 'label',
                        display: __('Label'),
                        type: 'text',
                        width: 75
                    },
                    {
                        handle: 'params',
                        display: __('Parameters'),
                        type: 'array'
                    }
                ]
            };
        },

        emailGridConfig: function() {
            return {
                add_row: __('Email'),
                mode: 'stacked',
                fields: [
                    {
                        handle: 'to',
                        display: __('Recipient (To)'),
                        type: 'text',
                        width: 50,
                        instructions: __('cp.formset_emails_grid_to_instructions')
                    },
                    {
                        handle: 'from',
                        display: __('Sender (From)'),
                        type: 'text',
                        width: 50,
                        instructions: __('cp.formset_emails_grid_from_instructions')
                    },
                    {
                        handle: 'reply_to',
                        display: __('Reply to'),
                        type: 'text'
                    },
                    {
                        handle: 'subject',
                        display: __('Subject'),
                        type: 'text',
                        instructions: __('cp.formset_emails_grid_subject_instructions')
                    },
                    {
                        handle: 'html',
                        display: __('HTML View'),
                        type: 'text',
                        instructions: __('cp.formset_emails_grid_html_instructions'),
                        width: 50
                    },
                    {
                        handle: 'text',
                        display: __('Text View'),
                        type: 'text',
                        instructions: __('cp.formset_emails_grid_view_instructions'),
                        width: 50
                    }
                ]
            };
        }
    },

    methods: {
        getBlankFormset: function() {
            this.formset = {
                title: '',
                fields: []
            };

            this.loading = false;
        },

        save: function() {
            if (! this.formset.title) {
                this.$notify.error(__('validation.required', { attribute: 'title' }));
                return;
            }

            this.$axios[this.saveMethod](this.saveUrl, {
                slug: this.slug,
                formset: this.formset
            }).then(function(response) {
                window.location = response.data.redirect;
            }).catch(e => {
                this.$notify.error(e.response.data.message)
            });
        }
    },

    created() {
        if (this.create) {
            this.getBlankFormset();
        }
    }
};
</script>
