export default {

    data() {
        return {
            showDrafts: true
        };
    },

    computed: {

        draftOptions() {
            return [
                { value: true, text: translate('cp.show_drafts') },
                { value: false, text: translate('cp.hide_drafts') }
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
