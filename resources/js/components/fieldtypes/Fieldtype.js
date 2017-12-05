export default {

    props: {
        data: {},
        config: {},
        name: {},
        leaveAlert: {
            default: false
        }
    },

    data() {
        return {
            autoBindChangeWatcher: true,
            changeWatcherIsBound: false,
            changeWatcherWatchDeep: true
        };
    },

    computed: {

        /**
         * Whether this field is nested somewhere inside a Grid fieldtype.
         */
        isInsideGridField() {
            let vm = this;

            while (true) {
                let parent = vm.$parent;

                if (! parent) return false;

                if (parent.constructor.name === 'GridFieldtype') {
                    return true;
                }

                vm = parent;
            }
        }

    },

    ready() {
        if (this.autoBindChangeWatcher) {
            this.bindChangeWatcher();
        }
    },

    methods: {

        bindChangeWatcher() {
            if (!this.leaveAlert) return;
            if (this.changeWatcherIsBound) return;

            this.$watch('data', function () {
                this.$dispatch('changesMade', true);
            }, { deep: this.changeWatcherWatchDeep });

            this.changeWatcherIsBound = true;
        },

        getReplicatorPreviewText() {
            return this.data;
        },

        focus() {
            this.$el.focus();
        }

    }

};
