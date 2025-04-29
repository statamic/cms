<template>
    <div>
        <slot :confirm="confirm" />

        <confirmation-modal
            v-if="confirming"
            :title="__('Are you sure?')"
            :danger="true"
            @confirm="disable"
            @cancel="confirming = false"
        >
            <p class="mb-2" v-html="__('statamic::messages.disable_two_factor_authentication')"></p>

            <p
                v-if="isCurrentUser && isEnforced"
                v-html="__('statamic::messages.disable_two_factor_authentication_current_user_enforced')"
            ></p>
            <p
                v-if="isCurrentUser && !isEnforced"
                v-html="__('statamic::messages.disable_two_factor_authentication_current_user_optional')"
            ></p>
            <p
                v-if="!isCurrentUser && isEnforced"
                v-html="__('statamic::messages.disable_two_factor_authentication_other_user_enforced')"
            ></p>
            <p
                v-if="!isCurrentUser && !isEnforced"
                v-html="__('statamic::messages.disable_two_factor_authentication_other_user_optional')"
            ></p>
        </confirmation-modal>
    </div>
</template>

<script>
import { requireElevatedSession } from '@statamic/components/elevated-sessions';

export default {
    props: {
        url: String,
        isCurrentUser: Boolean,
        isEnforced: Boolean,
    },

    data() {
        return {
            loading: false,
            confirming: false,
        };
    },

    watch: {
        loading(loading) {
            this.$progress.loading(loading);
        },
    },

    methods: {
        confirm() {
            requireElevatedSession()
                .then(() => (this.confirming = true))
                .catch(() => {});
        },

        disable() {
            this.loading = true;

            this.$axios
                .delete(this.url)
                .then((response) => {
                    this.$toast.success(__('Disabled two factor authentication'));

                    this.$emit('reset-complete');

                    if (response.data.redirect) {
                        window.location = response.data.redirect;
                    }
                })
                .catch((error) => this.$toast.error(error.message))
                .finally(() => {
                    this.loading = false;
                    this.confirming = false;
                });
        },
    },
};
</script>
