<template>
    <div>
        <div class="sticky flex items-center mb-3 w-full">
            <h1 class="flex-1" v-if="create">
                {{ translate('cp.create_formset') }}
            </h1>

            <h1 class="flex-1" v-if="!create">
                {{ translate('cp.editing_formset') }}:
                <strong>{{ formsetTitle }}</strong>
            </h1>

            <button type="button" class="btn btn-primary" v-on:click="save()">{{ translate('cp.save') }}</button>
        </div>

        <div class="px-3">
            <div class="card p-0">
                <div class="fieldset-builder">

                    <div class="form-group">
                        <label class="block">{{ translate('cp.title') }}</label>
                        <small class="help-block">{{ translate('cp.formset_title_instructions') }}</small>
                        <input type="text" class="form-control" v-model="formset.title" autofocus="autofocus" />
                    </div>

                    <div class="form-group" v-if="create">
                        <label class="block">{{ translate('cp.slug') }}</label>
                        <small class="help-block">{{ translate('cp.formset_slug_instructions') }}</small>
                        <input type="text" class="form-control" v-model="slug" />
                    </div>

                    <div class="form-group">
                        <label class="block">{{ translate_choice('cp.metrics', 2) }}</label>
                        <small class="help-block">{{ translate('cp.formset_metrics_instructions') }}</small>
                        <grid-fieldtype :value="formset.metrics" :config="metricsGridConfig" name="metrics" @updated="formset.metrics = $event"></grid-fieldtype>
                    </div>

                    <div class="form-group">
                        <label class="block">{{ translate_choice('cp.emails', 2) }}</label>
                        <small class="help-block">{{ translate('cp.formset_emails_instructions') }}</small>
                        <grid-fieldtype :value="formset.email" :config="emailGridConfig" name="email" @updated="formset.email = $event"></grid-fieldtype>
                    </div>

                    <div class="form-group">
                        <label class="block">{{ translate('cp.formset_honeypot_field') }}</label>
                        <small class="help-block">{{ translate('cp.formset_honeypot_instructions') }} <a href="https://docs.statamic.com/forms#honeypot">{{ translate('cp.formset_honeypot_link') }}</a></small>
                        <input type="text" class="form-control" v-model="formset.honeypot" />
                    </div>

                    <div class="form-group">
                        <label class="block">{{ translate('cp.formset_store_field') }}</label>
                        <small class="help-block">{{ translate('cp.formset_store_instructions') }}</small>
                        <toggle-fieldtype :value="formset.store" :config="{}" name="store"></toggle-fieldtype>
                    </div>

                </div>
            </div>

            <div class="card p-3">
                <div class="head clearfix">
                    <h2 class="m-0">{{ translate_choice('cp.fields', 2) }}</h2>
                    <small class="help-block">{{ translate('cp.formset_fields_instructions') }}</small>
                </div>

                <formset-fields-builder v-model="formset.fields"></formset-fields-builder>
            </div>
        </div>

    </div>
</template>

<script>
import axios from 'axios';

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
                add_row: translate('cp.formset_metrics_grid_add_row'),
                mode: 'stacked',
                fields: [
                    {
                        handle: 'type',
                        display: translate('cp.formset_metrics_grid_type_field'),
                        width: 25,
                        type: 'select',
                        options: [
                            { value: 'sum', text: translate('cp.formset_metrics_grid_type_option_sum') },
                            { value: 'total', text: translate('cp.formset_metrics_grid_type_option_total') },
                            { value: 'average', text: translate('cp.formset_metrics_grid_type_option_average') }
                        ]
                    },
                    {
                        handle: 'label',
                        display: translate('cp.formset_metrics_grid_label_field'),
                        type: 'text',
                        width: 75
                    },
                    {
                        handle: 'params',
                        display: translate('cp.formset_metrics_grid_params_field'),
                        type: 'array'
                    }
                ]
            };
        },

        emailGridConfig: function() {
            return {
                add_row: translate('cp.formset_emails_grid_add_row'),
                mode: 'stacked',
                fields: [
                    {
                        handle: 'to',
                        display: translate('cp.formset_emails_grid_to_field'),
                        type: 'text',
                        width: 50,
                        instructions: translate('cp.formset_emails_grid_to_instructions')
                    },
                    {
                        handle: 'from',
                        display: translate('cp.formset_emails_grid_from_field'),
                        type: 'text',
                        width: 50,
                        instructions: translate('cp.formset_emails_grid_from_instructions')
                    },
                    {
                        handle: 'reply_to',
                        display: translate('cp.formset_emails_grid_reply_to_field'),
                        type: 'text'
                    },
                    {
                        handle: 'subject',
                        display: translate('cp.formset_emails_grid_subject_field'),
                        type: 'text',
                        instructions: translate('cp.formset_emails_grid_subject_instructions')
                    },
                    {
                        handle: 'template',
                        display: translate('cp.formset_emails_grid_template_field'),
                        type: 'text',
                        instructions: translate('cp.formset_emails_grid_template_instructions')
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
                this.$notify.error(translate('validation.required', { attribute: 'title' }));
                return;
            }

            axios[this.saveMethod](this.saveUrl, {
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
