module.exports = {

    template: require('./field-settings.template.html'),

    props: ['field'],

    data: function() {
        return {
            isNameModified: true
        };
    },

    ready: function() {
        var self = this;

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
    }

};
