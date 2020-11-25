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
        <div slot-scope="{ setFieldValue, setFieldMeta }">
            <configure-sections
                @updated="setFieldValue"
                @meta-updated="setFieldMeta"
                :enable-sidebar="false" />

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
        url: String
    },

    data() {
        return {
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
                this.$toast.success(__('Saved'));
                this.$refs.container.saved();
            }).catch(e => this.handleAxiosError(e));
        },

        handleAxiosError(e) {
            this.saving = false;
            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data;
                this.error = message;
                this.errors = errors;
                this.$toast.error(message);
            } else {
                this.$toast.error(__('Something went wrong'));
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
