<template>
    <CreateForm
        :title="__('Create Blueprint')"
        :subtitle="__('messages.blueprints_intro')"
        :icon="icon"
        @submit="submit"
    >
        <ui-card-panel :heading="__('Blueprint Details')">
            <div class="space-y-8">
                <ui-field
                    :label="__('Title')"
                    :instructions="__('messages.blueprints_title_instructions')"
                    :instructions-below="true"
                >
                    <ui-input v-model="title" autofocus tabindex="1" />
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
        icon: { type: String },
    },

    data() {
        return {
            title: null,
        };
    },

    methods: {
        submit() {
            this.$axios
                .post(this.route, { title: this.title })
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
            this.submit();
        });
    },
};
</script>
