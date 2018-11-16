export default {

    data() {
        return {
            showDrafts: true
        };
    },

    computed: {

        draftOptions() {
            return [
                { value: true, text: __('Show Drafts') },
                { value: false, text: __('Hide Drafts') }
            ];
        }

    },

    mounted() {
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
