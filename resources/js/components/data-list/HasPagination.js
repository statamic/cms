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

    created() {
        this.$events.$on('per-page-changed', this.perPageChanged);
    },

    methods: {

        setInitialPerPage() {
            if (! this.hasPreferences) {
                return;
            }

            this.perPage = this.getPreference('per_page') || this.initialPerPage;
        },

        perPageChanged(perPage) {
            perPage = parseInt(perPage);

            let promise = this.hasPreferences
                ? this.setPreference('per_page', perPage != this.initialPerPage ? perPage : null)
                : Promise.resolve();

            promise.then(response => {
                this.perPage = perPage;
                this.pageReset();
            });
        },

        pageReset() {
            this.page = 1;
        }

    }

}
