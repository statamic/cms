<template>

    <publish-container
        v-if="blueprint"
        ref="container"
        name="collection"
        :blueprint="blueprint"
        :values="values"
        reference="collection"
        :meta="meta"
        :errors="errors"
        @updated="values = $event"
    >
        <div slot-scope="{ setFieldValue }">
            <configure-sections @updated="setFieldValue" :enable-sidebar="false"/>
            <div class="py-2 border-t flex justify-between">
                <a :href="url" class="btn" v-text="__('Cancel') "/>
                <button type="submit" class="btn-primary" @click="submit">{{ __('Save') }}</button>
            </div>
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
                window.location = response.data.redirect;
            }).catch(e => this.handleAxiosError(e));
        },

        handleAxiosError(e) {
            this.saving = false;
            if (e.response) {
                const { message, errors } = e.response.data;
                this.error = message;
                this.errors = errors;
                this.$toast.error(message);
            } else {
                this.$toast.error(__('Unable to save changes'));
            }
        },

    },

    created() {
        this.$keys.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            this.submit();
        });
    },

}
</script>
