var Conditionals = require('./conditionals.js');
var Vue = require('vue');

module.exports = {

    template: require('./fields.template.html'),

    index: [],

    mixins: [Conditionals],

    props: [
        'fieldData', 'builder', 'fields', 'errors', 'fieldset-name', 'uuid',
        'locale', 'editing-fieldset', 'selected-field', 'on-select', 'on-delete',
        'on-sort', 'env', 'removeTitle', 'focus',
    ],

    data: function () {
        return {
            loading: true,
            widths: [
                {value: 100, text: '100%'},
                {value: 75, text: '75%'},
                {value: 66, text: '66%'},
                {value: 50, text: '50%'},
                {value: 33, text: '33%'},
                {value: 25, text: '25%'}
            ],
        }
    },

    methods: {
        /**
         * Get the fieldset from the server
         */
        getFieldset: function() {
            var params = {};
            var url = cp_url('fieldsets/') + this.fieldsetName + '/get';

            params.locale = this.locale;
            this.$http.get(url, params).success(function(data) {
                if (this.removeTitle) {
                    // Remove the title field
                    var title = _.findIndex(data.fields, function(field) {
                        return field.name === 'title';
                    });
                    if (title !== -1) {
                        data.fields.splice(title, 1);
                    }
                }

                this.fields = data.fields;
                this.loading = false;

                /**
                 * Run the conditional validator only once the field and field data
                 * are bound.
                 *
                 * $nextdTick just ensures that it happens after everything else is done.
                 */
                this.$nextTick(() => {
                    this.runConditionals(this.fieldData);
                });

                this.$dispatch('fieldsetLoaded', data);
            });
        },

        componentName: function (field) {
            return field.type.replace('.', '-') + '-fieldtype';
        },

        /**
         * Bootstrap Column Width class
         * Takes a percentage based integer and converts it to a bootstrap column number
         * eg. 100 => 12, 50 => 6, etc.
         */
        colClass: function(width) {
            if (this.$root.isPreviewing) {
                return 'col-md-12';
            }

            width = width || 100;
            return 'col-md-' + Math.round(width / 8.333);
        },

        widthText: function(width) {
            var width = width || 100;
            return _.findWhere(this.widths, {value: width}).text;
        },

        sortableWatcher: function() {
            // Enable sorting if applicable and enable a watcher to turn it on/off
            if (this.editingFieldset) {
                this.enableSorting();
            }

            this.$watch('editingFieldset', function(val, oldval) {
                if (val) {
                    this.enableSorting();
                } else {
                    this.disableSorting();
                }
            });
        },

        /**
         * Set up sorting of fields
         */
        enableSorting: function() {
            var self = this;
            var dragindex = null;
            $(this.$el).siblings('.publish-fields').sortable({
                revert: 175,
                start: function(e, ui) {
                    // Modify the default placeholder
                    ui.placeholder.html('<div class="field-inner"></div>');

                    // Keep track of the index of the field when we pick it up
                    dragindex = ui.item.index();
                },
                update: function(e, ui) {
                    // When its dropped in another position, we'll need to update
                    // the order of the fields in the fieldset.
                    var dropindex = ui.item.index();

                    self.fields.splice(dropindex, 0, self.fields.splice(dragindex, 1)[0]);
                }
            });
        },

        /**
         * Disable sorting
         */
        disableSorting: function() {
            $(this.$el).siblings('.publish-fields').sortable('destroy');
        },

        editFieldset: function() {
            this.editingFieldset = true;
        },

        saveFieldset: function() {
            var self = this;

            var fields = _.map(this.fields, function(field) {
                return _.pick(field, 'name', 'width');
            });

            this.$http.post(cp_url('fieldsets/update-layout/') + this.fieldsetName, {
                fields: fields
            }).success(function(data) {
                self.editingFieldset = false;
            });
        },

        stopPreviewing: function() {
            this.$event.preventDefault();

            this.$root.$set('isPreviewing', false);
        },

        hasError: function(field) {
            return _.has(this.errors, 'fields.'+field.name);
        },

        definedInEnvironment: function(name) {
            return _.has(this.env, name);
        }
    },

    ready: function() {
        this.getFieldset();
        this.sortableWatcher();

        // The parent component will emit an event when the save button is clicked.
        this.$on('saveLayout', function() {
            this.saveFieldset();
        });
    }
};
