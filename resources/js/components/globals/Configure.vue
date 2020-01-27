<template>

    <div>
        <stack name="configure-global" v-if="editing" @closed="editing = false">
            <div class="h-full overflow-auto p-3 bg-grey-30">
                <div v-if="saving" class="absolute pin z-200 flex items-center justify-center text-center">
                    <loading-graphic :text="__('Saving')" />
                </div>

                <div class="flex items-center mb-3">
                    <h1 class="flex-1">
                        <small class="subhead block" v-text="__('Globals')" />
                        {{ __('Edit Global Set') }}
                    </h1>
                    <button class="btn mr-2" @click="editing = false" v-text="__('Cancel')" />
                    <button class="btn btn-primary" @click="save" v-text="__('Save')" />
                </div>

                <div class="card publish-fields">
                    <form-group
                        handle="title"
                        display="Title"
                        :instructions="__('messages.global_set_title_instructions')"
                        v-model="title"
                        :errors="errors.title"
                        width="50"
                    />
                    <form-group
                        fieldtype="slug"
                        handle="handle"
                        :display="__('Handle')"
                        :instructions="__('messages.global_set_handle_instructions')"
                        :config="{ slugify_using: 'instance' }"
                        v-model="handle"
                        :errors="errors.handle"
                        width="50"
                    />

                    <form-group
                        handle="blueprint"
                        fieldtype="blueprints"
                        :config="{ component: 'relationship', max_items: 1 }"
                        :display="__('Blueprint')"
                        :instructions="__('messages.global_set_blueprint_instructions')"
                        v-model="blueprint"
                        :errors="errors.blueprint"
                    />
                </div>
            </div>
        </stack>

        <button
            class="btn"
            v-text="__('Edit')"
            @click="editing = true"
        />
    </div>

</template>

<script>
import Popper from 'vue-popperjs';

export default {

    components: {
        Popper
    },

    props: {
        saveUrl: String,
        id: String,
        initialTitle: String,
        initialHandle: String,
        initialBlueprint: String
    },

    data() {
        return {
            editing: false,
            saving: false,
            error: null,
            errors: {},
            title: this.initialTitle,
            handle: this.initialHandle,
            blueprint: this.initialBlueprint ? [this.initialBlueprint] : [],
        }
    },

    computed: {

        hasErrors() {
            return this.error || Object.keys(this.errors).length;
        },

        values() {
            return {
                title: this.title,
                handle: this.handle,
                blueprint: this.blueprint.length ? this.blueprint[0] : null
            }
        }

    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            this.clearErrors();
            this.saving = true;

            this.$axios.patch(this.saveUrl, this.values).then(response => {
                window.location.reload();
            }).catch(e => {
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                    this.$toast.error(message);
                    this.saving = false;
                } else {
                    this.$toast.error(__('Something went wrong'));
                }
            })
        }

    }

}
</script>
