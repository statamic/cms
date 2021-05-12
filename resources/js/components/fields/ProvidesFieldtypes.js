export default {

    computed: {

        fieldtypes() {
            if (this.fieldtypesLoading) return;

            return this.$store.state.statamic.fieldtypes;
        },

        fieldtypesLoading() {
            return this.$store.state.statamic.fieldtypes === 'loading';
        },

        fieldtypesLoaded() {
            return Array.isArray(this.fieldtypes);
        }

    },

    created() {
        if (this.fieldtypes || this.fieldtypesLoading) return;

        this.$store.commit('statamic/fieldtypes', 'loading');

        let url = cp_url('fields/fieldtypes?selectable=true');

        if (this.$config.get('isFormBlueprint')) url += '&forms=true';

        this.$axios.get(url).then(response => this.$store.commit('statamic/fieldtypes', response.data));
    }

}
