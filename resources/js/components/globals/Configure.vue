<template>


    <div>
        <popper ref="popper" trigger="click" :append-to-body="true" :options="{ placement: 'left-start' }">
            <div class="popover w-96">
                <div class="saving flex justify-center text-center" v-if="saving">
                    <loading-graphic :text="__('Saving')" />
                </div>
                <div class="publish-fields">
                    <form-group
                        handle="title"
                        display="Title"
                        :instructions="__('global_set_title_instructions')"
                        v-model="set.title"
                        :errors="errors.title"
                        class="p-0 mb-3"
                    />
                    <form-group
                        fieldtype="slug"
                        handle="handle"
                        :display="__('Handle')"
                        :instructions="__('global_set_handle_instructions')"
                        v-model="set.handle"
                        :errors="errors.handle"
                        class="p-0 mb-3"
                    />
                    <form-group
                        handle="blueprint"
                        display="Blueprint"
                        :instructions="__('global_set_blueprint_instructions')"
                        v-model="set.blueprint"
                        :errors="errors.blueprint"
                        class="p-0 mb-3"
                    />
                </div>
                <button class="btn btn-primary" @click.prevent="save">{{ __('Save') }}</button>
            </div>

            <button
                slot="reference"
                class="btn"
                v-text="__('Edit')"
            />
        </popper>
    </div>

</template>

<script>
import axios from 'axios';
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
            saving: false,
            error: null,
            errors: {},
            set: {
                title: this.initialTitle,
                handle: this.initialHandle,
                blueprint: this.initialBlueprint
            }
        }
    },

    computed: {

        hasErrors() {
            return this.error || Object.keys(this.errors).length;
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

            axios.patch(this.saveUrl, this.set).then(response => {
                window.location.reload();
            }).catch(e => {
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                    this.$notify.error(message, { timeout: 2000 });
                    this.saving = false;
                } else {
                    this.$notify.error('Something went wrong');
                }
            })
        }

    }

}
</script>
