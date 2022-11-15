<template>

        <div>
            <publish-fields-container class="card p-0 mb-3">

                <form-group
                    :display="__('Title')"
                    handle="title"
                    :errors="errors.title"
                    :instructions="__('messages.user_groups_title_instructions')"
                    v-model="title"
                    :focus="true"
                />

                <form-group
                    fieldtype="slug"
                    :display="__('Handle')"
                    handle="handle"
                    :instructions="__('messages.user_groups_handle_instructions')"
                    :errors="errors.title"
                    v-model="handle"
                />

                <div class="text-xs text-red p-3 pt-0" v-if="initialHandle && handle != initialHandle">
                    {{ __('messages.role_change_handle_warning') }}
                </div>

                <div class="form-group publish-field w-full" v-if="$permissions.has('assign roles')">
                    <div class="field-inner">
                        <label class="publish-field-label" v-text="__('Roles')" />
                        <div class="help-block -mt-1">
                            <p>{{ __('messages.user_groups_role_instructions') }}</p>
                        </div>
                    </div>
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

            </publish-fields-container>
            <div class="py-2 border-t flex justify-between">
                <a :href="action" class="btn" v-text="__('Cancel') "/>
                <button type="submit" class="btn-primary" @click="save">{{ __('Save') }}</button>
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
        method: String,
        creating: Boolean
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
            if (this.creating) {
                this.handle = this.$slugify(display, '_');
            }
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
                window.location = response.data.redirect;
            }).catch(e => {
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                    this.$toast.error(message);
                } else {
                    this.$toast.error(__('Unable to save user group'));
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
