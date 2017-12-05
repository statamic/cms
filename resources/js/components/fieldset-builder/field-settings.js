module.exports = {

    components: {
        fieldConditionsBuilder: require('../field-conditions-builder/FieldConditionsBuilder.vue')
    },

    template: require('./field-settings.template.html'),

    props: ['field', 'fieldtypeConfig', 'fieldtypes', 'root', 'isTaxonomy'],

    data: function() {
        return {
            isNameModified: true,
            widths: [
                {value: 100, text: 'Full width'},
                {value: 50, text: 'Half'},
                {value: 25, text: '1/4 - One quarter'},
                {value: 75, text: '3/4 - Three quarters'},
                {value: 33, text: '1/3 - One third'},
                {value: 66, text: '2/3 - Two thirds'}
            ]
        };
    },

    computed: {
        selectedWidth: function() {
            var width = this.field.width || 100;
            var found = _.findWhere(this.widths, {value: width});
            return found.text;
        },

        fieldtype: function() {
            return _.findWhere(this.fieldtypes, { name: this.field.type });
        },

        canBeLocalized: function() {
            if (this.isTaxonomy) return false;

            return this.root && Statamic.locales.length > 1 && this.fieldtype.canBeLocalized;
        },

        canBeValidated: function() {
            if (this.isTaxonomy) return false;

            return this.fieldtype.canBeValidated;
        },

        canHaveDefault: function() {
            return this.fieldtype.canHaveDefault;
        }
    },

    ready: function() {
        var self = this;

        this.root = Boolean(this.root || false);

        // For new fields, we'll slugify the display name into the field name.
        // If they edit the name, we'll stop.
        if (this.field.isNew) {
            this.isNameModified = false;
            delete this.field.isNew;

            this.$watch('field.display', function(display) {
                if (! this.isNameModified) {
                    this.field.name = this.$slugify(display, '_');
                }
            });
        }

        // Add default values
        _.each(this.fieldtypeConfig, function(configField) {
            if (self.field[configField.name] === undefined) {
                var defaultVal = configField.default || null;
                Vue.set(self.field, configField.name, defaultVal);
            }
        });
    }

};
