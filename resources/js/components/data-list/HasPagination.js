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
        this.$events.$on('filters-changed', this.pageReset);
    },

    methods: {

        setInitialPerPage() {
            if (! this.hasPreferences) {
                return;
            }

            this.perPage = this.getPreference('per_page') || this.initialPerPage;

            // TODO: Remove after figuring out below caching issue
            console.log('Preferred Per Page: ' + this.getPreference('per_page'));
            console.log('Initial Per Page: ' + this.initialPerPage);
            console.log('Set Per Page: ' + this.perPage);
        },

        perPageChanged(perPage) {
            this.perPage = perPage;
            this.pageReset();

            // TODO: Why is there caching issues with this, but not when filter preferences are saved?
            if (this.hasPreferences) {
                this.setPreference('per_page', this.perPage != this.initialPerPage ? this.perPage : null);
            }
        },

        pageReset() {
            this.page = 1;
        }

    }

}
