export default {

    data() {
        return {
            showDrafts: true
        };
    },

    computed: {

        draftOptions() {
            return [
                { value: true, text: 'Show Drafts' },
                { value: false, text: 'Hide Drafts' }
            ];
        }

    },

    ready() {
        this.showDrafts = this.getInitialShowDrafts();
    },

    methods: {

        getInitialShowDrafts() {
            const key = this.getShowDraftsLocalStorageKey();

            if (! key) return true;

            if (localStorage.getItem(key) === 'false') {
                return false;
            }

            return true;
        },

        getShowDraftsLocalStorageKey() {
            return 'statamic.drafts';
        },

        onShowDraftsChanged() {
            //
        },

        bindShowDraftsWatcher() {
            this.$watch('showDrafts', (value) => {
                this.onShowDraftsChanged();
                const key = this.getShowDraftsLocalStorageKey();
                if (key) localStorage.setItem(key, value);
            });
        }

    }

};
