<template>
    <CreateForm
        :title="__('Create Collection')"
        :subtitle="__('messages.collection_configure_intro')"
        icon="collections"
        @submit="submit"
    >
        <ui-card-panel :heading="__('Collection Details')">
            <div class="space-y-8">
                <ui-field :label="__('Title')" :instructions="__('messages.collection_configure_title_instructions')" :instructions-below="true">
                    <ui-input v-model="title" autofocus tabindex="1" />
                </ui-field>
                <ui-field :label="__('Handle')" :instructions="__('messages.collection_configure_handle_instructions')" :instructions-below="true">
                    <ui-input v-model="handle" tabindex="2" :loading="slug.busy" />
                </ui-field>
            </div>
        </ui-card-panel>
    </CreateForm>
</template>

<script setup>
import { CreateForm } from '@statamic/ui';
</script>

<script>
export default {
    props: {
        route: { type: String },
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
