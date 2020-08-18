export default {

    props: {
        initialPerPage: {
            type: Number,
            default() {
                return Statamic.$config.get('paginationSize');
            }
        }
    },

    data() {
        return {
            perPage: this.initialPerPage,
            page: 1,
        }
    },

    mounted() {
        this.setInitialPerPage();
    },

    methods: {

        setInitialPerPage() {
            if (! this.hasPreferences) {
                return;
            }

            this.perPage = this.getPreference('per_page') || this.initialPerPage;
        },

        changePerPage(perPage) {
            perPage = parseInt(perPage);

            let promise = this.hasPreferences
                ? this.setPreference('per_page', perPage != this.initialPerPage ? perPage : null)
                : Promise.resolve();

            promise.then(response => {
                this.perPage = perPage;
                this.resetPage();
            });
        },

        selectPage(page) {
            this.page = page;
        },

        resetPage() {
            this.page = 1;
        },

    }

}
