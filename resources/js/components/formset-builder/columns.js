module.exports = {

    template: require('./columns.template.html'),

    props: {
        columns: { type: Array },
        fields: { type: Array }
    },

    computed: {
        suggestions: function() {
            var suggestions = [];

            _.each(this.fields, function(field) {
                suggestions.push({
                    text: field.display,
                    value: field.name
                });
            });

            return suggestions;
        }
    }

};
