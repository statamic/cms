Mousetrap = require('mousetrap');

module.exports = {

    components: {
        TaxonomyFieldsBuilder: require('./TaxonomyFieldsBuilder.vue')
    },

    template: require('./builder.template.html'),

    props: {
        'fieldsetTitle': String,
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
            fieldset: { title: '', fields: [] },
            fieldtypes: []
        }
    },

    computed: {
        canSave() {
            return this.fieldset.title !== '';
        }
    },

    methods: {
        getFieldtypes: function() {
            var self = this;
            this.$http.get(cp_url('/fieldtypes')).success(function(data) {
                _.each(data, function(fieldtype) {
                    self.fieldtypes.push(fieldtype);
                });

                self.getFieldset();
            });
        },

        getFieldset: function() {
            var self = this;

            var url = cp_url('/fieldsets/' + get_from_segment(3) + '/get');

            self.$http.get(url, {
                partials: false,
                editing: true,
                creating: this.create
            }).success(function (data) {
                var fieldset = this.registerFieldKeys(data);

                // Delete keys we dont need.
                fieldset.fields = _.map(fieldset.fields, function(field) {
                    delete field.complete;
                    delete field.html;
                    return field
                });

                self.fieldset = fieldset;
                self.loading = false;

                // Add the watcher after the request is complete otherwise it will
                // be marked as changed even though the user did nothing.
                this.$watch('fieldset', () => {
                    this.$dispatch('changesMade', true);
                }, { deep: true });
            }).error(function (data) {
                console.log(data);
                self.errorMessage = data.message;
            });
        },

        /**
         * Register keys in the fields
         *
         * Vue works better when the array of data that we'll be touching already
         * contains the keys. Here we'll go ahead and add the keys from the
         * config if they don't already exist in the fieldset.
         */
        registerFieldKeys: function(fieldset) {
            var self = this;

            fieldset.fields = _.map(fieldset.fields, function(field) {
                var config = _.findWhere(self.fieldtypes, { name: field.type }).config;

                _.each(config, function(configField) {
                    if (field[configField.name] === undefined) {
                        field[configField.name] = null;
                    }
                });

                return field;
            });

            return fieldset;
        },

        save: function() {
            this.$http.post(this.saveUrl, {
                slug: this.slug,
                fieldset: this.fieldset
            }).success(function(data) {
                this.$dispatch('changesMade', false);
                window.location = data.redirect;
            });
        }
    },

    ready: function() {
        this.getFieldtypes();

        Mousetrap.bindGlobal('mod+s', (e) => {
            e.preventDefault();

            this.save();
        });
    }
};
