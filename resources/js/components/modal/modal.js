module.exports = {

    template: require('./modal.template.html'),

    props: {
        show: {
            type: Boolean,
            required: true,
            default: false
        },
        full: {
            type: Boolean,
            required: false,
            default: false
        },
        class: {
            required: false,
            default: function() {
                return {};
            }
        },
        loading: Boolean,
        saving: Boolean
    },

    computed: {
        classes: function() {
            var defaults = {
                'modal-full': this.full
            };

            var classes = {};
            if (typeof this.class === 'string') {
                _.each(this.class.split(' '), function(c) {
                    classes[c] = true;
                });
            } else {
                classes = this.class;
            }

            return $.extend({}, defaults, classes);
        }
    },

    methods: {
        close: function() {
            this.show = false
        }
    },

    ready: function() {
        Mousetrap.bind('esc', function(e) {
            this.close();
        }.bind(this), 'keyup');
    },

    events: {
        'close-modal': function () {
            this.show = false;
        }
    }
};
