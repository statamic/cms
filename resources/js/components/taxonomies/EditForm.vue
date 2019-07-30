<template>

    <publish-container
        v-if="blueprint"
        ref="container"
        name="taxonomy"
        :fieldset="blueprint"
        :values="values"
        reference="taxonomy"
        :meta="meta"
        :errors="errors"
        @updated="values = $event"
    >
        <div slot-scope="{ setValue }">

            <div class="flex items-center mb-3">
                <h1 class="flex-1">
                    <small class="subhead block">
                        <a :href="listingUrl" v-text="__('Taxonomies')" />
                    </small>

                    {{ title }}
                </h1>
                <button type="submit" class="btn btn-primary" @click="submit">{{ __('Save') }}</button>
            </div>

            <publish-sections @updated="setValue" :enable-sidebar="false"/>
        </div>
    </publish-container>

</template>

<script>
export default {

    props: {
        blueprint: Object,
        initialValues: Object,
        meta: Object,
        initialTitle: String,
        url: String,
        listingUrl: String,
    },

    data() {
        return {
            title: this.initialTitle,
            values: this.initialValues,
            error: null,
            errors: {},
        }
    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        submit() {
            this.saving = true;
            this.clearErrors();

            this.$axios.patch(this.url, this.values).then(response => {
                this.saving = false;
                this.title = response.data.title;
                this.$notify.success('Saved');
                this.$refs.container.saved();
            }).catch(e => this.handleAxiosError(e));
        },

        handleAxiosError(e) {
            this.saving = false;
            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data;
                this.error = message;
                this.errors = errors;
                this.$notify.error(message);
            } else {
                this.$notify.error('Something went wrong');
            }
        },

    }

}
</script>
