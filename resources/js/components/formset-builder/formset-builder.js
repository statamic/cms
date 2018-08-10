module.exports = {

    template: require('./formset-builder.template.html'),

    components: {
        'formset-fields-builder': require('./formset-fields'),
        'formset-columns': require('./columns')
    },

    props: {
        'formsetTitle': String,
        'formsetName': String,
        'create': {
            type: Boolean,
            default: false
        },
        'saveUrl': String
    },

    data: function () {
        return {
            loading: true,
            errorMessage: null,
            slug: null,
            formset: { fields: [], columns: [] }
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
                        name: 'type',
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
                        name: 'label',
                        display: translate('cp.formset_metrics_grid_label_field'),
                        type: 'text',
                        width: 75
                    },
                    {
                        name: 'params',
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
                        name: 'to',
                        display: translate('cp.formset_emails_grid_to_field'),
                        type: 'text',
                        width: 50,
                        instructions: translate('cp.formset_emails_grid_to_instructions')
                    },
                    {
                        name: 'from',
                        display: translate('cp.formset_emails_grid_from_field'),
                        type: 'text',
                        width: 50,
                        instructions: translate('cp.formset_emails_grid_from_instructions')
                    },
                    {
                        name: 'reply_to',
                        display: translate('cp.formset_emails_grid_reply_to_field'),
                        type: 'text'
                    },
                    {
                        name: 'subject',
                        display: translate('cp.formset_emails_grid_subject_field'),
                        type: 'text',
                        instructions: translate('cp.formset_emails_grid_subject_instructions')
                    },
                    {
                        name: 'template',
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

        getFormset: function() {
            var self = this;
            var url = cp_url('/forms/' + this.formsetName + '/get');
            self.$http.get(url).success(function (data) {
                self.formset = data;
                self.loading = false;
            }).error(function (data) {
                self.errorMessage = data.message;
            });
        },

        save: function() {
            if (! this.formset.title) {
                this.$dispatch(
                    'setFlashError',
                    translate('validation.required', { attribute: 'title' })
                );

                return;
            }

            this.$http.post(this.saveUrl, {
                slug: this.slug,
                formset: this.formset
            }).success(function(data) {
                window.location = data.redirect;
            });
        }
    },

    mounted() {
        if (this.create) {
            this.getBlankFormset();
        } else {
            this.getFormset();
        }
    }
};
