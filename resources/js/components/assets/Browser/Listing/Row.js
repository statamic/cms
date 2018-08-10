import { mixin as clickaway } from 'vue-clickaway';

export default {
    mixins: [ clickaway ],

    data () {
        return {
            showActionsDropdown: false
        }
    },

    events: {
        'close-dropdown': function (reference) {
            if (this == reference) {
                return;
            }

            this.showActionsDropdown = false;
        },
    },

    methods: {
        toggleActions() {
            this.$emit('open-dropdown', this);

            this.showActionsDropdown = !this.showActionsDropdown;
        },

        away() {
            this.showActionsDropdown = false;
        }
    },
}
