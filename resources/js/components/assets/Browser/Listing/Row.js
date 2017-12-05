export default {
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
    },
}
