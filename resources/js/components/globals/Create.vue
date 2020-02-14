<template>

    <div>

        <div class="flex items-center mb-3">
            <slot name="header" />
            <button type="submit" class="btn-primary" @click="save">{{ __('Save') }}</button>
        </div>

        <div class="card publish-fields p-0">

            <form-group
                handle="title"
                :display="__('Title')"
                :instructions="__('messages.global_set_title_instructions')"
                v-model="title"
                :errors="errors.title"
                width="50"
                autofocus
            />

            <slugify
                :from="title"
                v-model="handle"
                separator="_"
            >
                <form-group
                    slot-scope="{ }"
                    fieldtype="slug"
                    handle="handle"
                    :display="__('Handle')"
                    :instructions="__('messages.global_set_handle_instructions')"
                    :value="handle"
                    @input="handle = $event"
                    :config="{ generate: false }"
                    :errors="errors.handle"
                    width="50"
                />
            </slugify>

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

</template>


<script>
export default {

    props: {
        action: String,
    },

    data() {
        return {
            saving: false,
            error: null,
            errors: {},
            title: null,
            handle: null,
            blueprint: null
        }
    },

    computed: {

        values() {
            return {
                title: this.title,
                handle: this.handle,
                blueprint: this.blueprint ? this.blueprint[0] : null
            }
        }

    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            this.saving = true;
            this.clearErrors();

            this.$axios.post(this.action, this.values).then(response => {
                this.saving = false;
                window.location = response.data.redirect;
            }).catch(e => {
                this.saving = false;
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                    this.$toast.error(message);
                } else {
                    this.$toast.error(__('Something went wrong'));
                }
            })
        }

    }


}
</script>
