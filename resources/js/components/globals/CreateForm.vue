<template>
    <CreateForm
        :title="__('Create Global Set')"
        :subtitle="__('messages.globals_configure_intro')"
        icon="globals"
        @submit="submit"
    >
        <ui-card-panel :heading="__('Global Set Details')">
            <div class="space-y-8">
                <ui-field :label="__('Title')" :instructions="__('messages.globals_configure_title_instructions')" :instructions-below="true">
                    <ui-input v-model="title" autofocus tabindex="1" />
                </ui-field>
                <ui-field :label="__('Handle')" :instructions="__('messages.globals_configure_handle_instructions')" :instructions-below="true">
                    <ui-input v-model="handle" tabindex="2" :loading="slug.busy" />
                </ui-field>
            </div>
        </ui-card-panel>
    </CreateForm>
</template>

<script>
import { CreateForm } from '@statamic/ui';

export default {
    props: {
        route: { type: String },
    },

    components: {
        CreateForm,
    },
    data() {
        return {
            title: null,
            handle: null,
            slug: this.$slug.async().separatedBy('_'),
        };
    },

    watch: {
        title(title) {
            this.slug.create(title).then((slug) => (this.handle = slug));
        },
    },

    methods: {
        submit() {
            this.$axios
                .post(this.route, { title: this.title, handle: this.handle })
                .then((response) => {
                    window.location = response.data.redirect;
                })
                .catch((error) => {
                    this.$toast.error(error.response.data.message);
                });
        },
    },

    mounted() {
        this.$keys.bindGlobal(['return'], (e) => {
            if (this.canSubmit) {
                this.submit();
            }
        });
    },
};
</script>
