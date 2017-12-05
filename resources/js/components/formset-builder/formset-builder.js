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
                add_row: 'Metric',
                mode: 'stacked',
                fields: [
                    {
                        name: 'type',
                        display: 'Metric Type',
                        width: 25,
                        type: 'select',
                        options: [
                            { value: 'sum', text: 'Sum' },
                            { value: 'total', text: 'Total' },
                            { value: 'average', text: 'Average' }
                        ]
                    },
                    {
                        name: 'label',
                        display: 'Label',
                        type: 'text',
                        width: 75
                    },
                    {
                        name: 'params',
                        display: 'Parameters',
                        type: 'array'
                    }
                ]
            };
        },

        emailGridConfig: function() {
            return {
                add_row: 'Email',
                mode: 'stacked',
                fields: [
                    { name: 'to', display: 'Recipient (To)', type: 'text', width: 50, instructions: 'Email address of the recipient.' },
                    { name: 'from', display: 'Sender (From)', type: 'text', width: 50, instructions: 'Leave blank to fall back to the site default.' },
                    { name: 'reply_to', display: 'Reply to', type: 'text' },
                    { name: 'subject', display: 'Subject', type: 'text', instructions: 'Email subject line.' },
                    { name: 'template', display: 'Template', type: 'text', instructions: 'Leave blank to use an automagic email.' }
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

    ready: function() {
        if (this.create) {
            this.getBlankFormset();
        } else {
            this.getFormset();
        }
    }
};
