export default {

    data: function () {
        return {
            blank: {},
            sortableOptions: {},
            autoBindChangeWatcher: false,
            changeWatcherWatchDeep: false,
            accordionMode: true
        };
    },

    ready() {
        this.accordionMode = this.getAccordionModeFromStorage();

        if (this.accordionMode) this.collapseAll();
    },

    methods: {

        setConfig: function (type) {
            return _.findWhere(this.config.sets, { name: type });
        },

        deleteSet: function (index) {
            this.data.splice(index, 1);
        },

        expandAll: function () {
            _.each(this.$refs.set, set => {
                if (typeof set.expand === 'function') {
                    set.expand(true)
                }
            });
            this.setAccordionMode(false);
        },

        collapseAll: function () {
            _.each(this.$refs.set, set => {
                if (typeof set.collapse === 'function') {
                    set.collapse(true)
                }
            });
            this.setAccordionMode(true);
        },

        getAccordionModeFromStorage() {
            let mode = this.accordionMode;
            const stored = localStorage.getItem('statamic.replicator.accordion');

            if (stored === 'true') {
                mode = true;
            } else if (stored === 'false') {
                mode = false;
            }

            return mode;
        },

        setAccordionMode(mode) {
            this.accordionMode = mode;
            localStorage.setItem('statamic.replicator.accordion', mode);
        }

    }

}