export default {

    data() {
        return {
            preferencesPrefix: null,
        }
    },

    computed: {

        hasPreferences() {
            return this.preferencesPrefix !== null;
        }

    },

    methods: {

        preferencesKey(type) {
            return `${this.preferencesPrefix}.${type}`;
        },

        getPreference(type) {
            return this.$preferences.get(this.preferencesKey(type));
        },

        setPreference(type, value) {
            return this.$preferences.set(this.preferencesKey(type), value);
        },

        removePreference(type, value=null) {
            return this.$preferences.remove(this.preferencesKey(type), value);
        }

    }

}
