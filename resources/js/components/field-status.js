module.exports = {
    props: ['entry', 'key',
        'type', 'primary', 'is'], // these prevent warnings in alpha

    computed: {
        statusClass: function() {
            var s = this.entry.published ? 'live' : 'hidden';

            return 'status status-' + s;
        }
    },

    template: '<span :class="statusClass"></span>'

};