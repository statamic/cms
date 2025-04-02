<template>
    <div>
        <div class="mb-8">
            <div class="mb-2 font-semibold">{{ __('Your account has been locked') }}</div>

            <div class="mb-4 text-xs text-gray-700">
                <p class="mb-1">{{ __('You need to speak to your administrator to unlock your account.') }}</p>
            </div>

            <div class="flex space-x-2">
                <button @click.prevent="confirming = true" class="btn">{{ __('Unlock account') }}</button>
            </div>
        </div>

        <confirmation-modal
            v-if="confirming"
            :title="__('Unlock account?')"
            :danger="true"
            @confirm="action"
            @cancel="confirming = false"
        >
            <p class="mb-2" v-html="__('statamic::messages.two_factor_unlock_confirm_1')"></p>
            <p class="mb-2">{{ __('statamic::messages.two_factor_unlock_confirm_2') }}</p>
            <p class="font-medium text-red-500">{{ __('statamic::messages.two_factor_unlock_confirm_3') }}</p>
        </confirmation-modal>
    </div>
</template>

<script>
export default {
    props: {
        route: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            confirming: false,
        };
    },

    computed: {
        timerId() {
            return 'statamic-two-factor-locked-' + this._uid;
        },
    },

    methods: {
        action() {
            // start the progress timer
            this.$progress.start(this.timerId);

            fetch(this.route, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': Statamic.$config.get('csrfToken'),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                //body: new FormData(this.form)
            })
                .then((res) => res.json())
                .then((data) => {
                    // notify
                    this.$toast.success(__('Account has been unlocked.'));

                    // emit the update
                    this.$emit('update', 'locked', false);
                })
                .catch((error) => {
                    // a js error took place
                    this.$toast.error(error.message);
                })
                .finally(() => {
                    // always executed
                    this.$progress.complete(this.timerId);
                    this.confirming = false;
                });
        },
    },
};
</script>
