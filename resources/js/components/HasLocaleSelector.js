export default {

    data() {
        return {
            locale: null
        };
    },

    computed: {

        locales() {
            return _.map(Statamic.$config.get('locales'), (locale, handle) => {
                return { text: locale.name, value: handle };
            });
        }

    },

    mounted() {
        this.locale = this.getInitialLocale();
    },

    methods: {

        getInitialLocale() {
            const defaultLocale = Object.keys(Statamic.$config.get('locales'))[0];

            if (Object.keys(Statamic.$config.get('locales')).length === 1) return defaultLocale;

            const key = this.getLocaleLocalStorageKey();

            if (! key) return defaultLocale;

            return localStorage.getItem(key) || defaultLocale;
        },

        getLocaleLocalStorageKey() {
            return 'statamic.locale';
        },

        onLocaleChanged() {
            //
        },

        bindLocaleWatcher() {
            this.$watch('locale', (value) => {
                this.onLocaleChanged();
                const key = this.getLocaleLocalStorageKey();
                if (key) localStorage.setItem(key, value);
            });
        }

    }

};
