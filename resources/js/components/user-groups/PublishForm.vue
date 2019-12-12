<template>

        <div>
            <div class="flex items-center mb-3">
                <slot name="heading" :title="initialTitle" />
                <button type="submit" class="btn btn-primary" @click.prevent="save">{{ __('Save') }}</button>
            </div>

            <div class="card p-0 mb-3 publish-fields">

                <form-group
                    :display="__('Title')"
                    handle="title"
                    width="50"
                    :errors="errors.title"
                    v-model="title"
                    autofocus
                />

                <form-group
                    fieldtype="slug"
                    :display="__('Handle')"
                    handle="handle"
                    width="50"
                    :errors="errors.title"
                    v-model="handle"
                />

                <div class="text-xs text-red p-3 pt-0" v-if="initialHandle && handle != initialHandle">
                    {{ __('messages.role_change_handle_warning') }}
                </div>

                <div class="form-group publish-field w-1/2">
                    <label class="publish-field-label" v-text="__('Roles')" />
                    <publish-field-meta
                        :config="{ handle: 'roles', type: 'user_roles' }"
                        :initial-value="roles">
                        <div slot-scope="{ meta, value, loading }">
                            <relationship-fieldtype
                                v-if="!loading"
                                :config="{ handle: 'roles', type: 'user_roles', mode: 'select' }"
                                :value="value"
                                :meta="meta"
                                handle="roles"
                                @input="roles = $event" />
                        </div>
                    </publish-field-meta>
                    <small class="help-block text-red mt-1 mb-0" v-if="errors.roles" v-text="errors.roles[0]" />
                </div>

            </div>

        </div>
</template>


<script>
export default {

    props: {
        initialTitle: String,
        initialHandle: String,
        initialRoles: Array,
        initialUsers: Array,
        action: String,
        method: String
    },

    data() {
        return {
            error: null,
            errors: {},
            title: this.initialTitle,
            handle: this.initialHandle,
            roles: this.initialRoles,
            users: this.initialUsers,
        }
    },

    watch: {
        'title': function(display) {
            this.handle = this.$slugify(display, '_');
        }
    },

    computed: {

        hasErrors() {
            return this.error || Object.keys(this.errors).length;
        },

        payload() {
            return {
                title: this.title,
                handle: this.handle,
                roles: this.roles,
                users: this.users,
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

            this.$axios[this.method](this.action, this.payload).then(response => {
                this.$toast.success('Saved');
                if (!this.initialHandle || (this.initialHandle !== this.handle)) {
                    window.location = response.data.redirect;
                }
            }).catch(e => {
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                    this.$toast.error(message);
                } else {
                    this.$toast.error('Something went wrong');
                }
            });
        }

    },

    mounted() {
        this.$keys.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            this.save();
        });
    }

}
</script>
