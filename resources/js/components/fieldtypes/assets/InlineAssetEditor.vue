<script>
export default {

    props: {
        asset: { required: true, type: Object }
    },

    data() {
        return {
            saving: false,
            recentlySaved: false,
            error: null,
            values: this.asset.values,
        }
    },

    render() {
        return this.$scopedSlots.default({
            values: this.values,
            setFieldValue: this.setFieldValue,
            submit: this.submit,
            error: this.error,
            saving: this.saving,
            recentlySaved: this.recentlySaved,
        });
    },

    methods: {

        setFieldValue(handle, value) {
            this.values = {...this.values, [handle]: value};
        },

        submit() {
            this.saving = true;
            this.recentlySaved = false;
            const url = cp_url(`assets/${utf8btoa(this.asset.id)}`);

            this.$axios.patch(url, this.values).then(response => {
                this.$emit('saved', response.data.asset);
                this.saving = false;
                this.recentlySaved = true;
                setTimeout(() => this.recentlySaved = false, 1000);
                this.error = null;
            }).catch(e => {
                this.saving = false;

                if (e.response && e.response.status === 422) {
                    this.error = Object.values(e.response.data.errors)[0][0];
                } else if (e.response) {
                    this.error = e.response.data.message;
                } else {
                    this.error = __('Something went wrong');
                }
            });
        }

    }

}
</script>
