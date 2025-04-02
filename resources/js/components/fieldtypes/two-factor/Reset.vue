<template>
    <div>
        <div>
            <div class="font-semibold mb-2">{{ __('statamic-two-factor::profile.reset.title') }}</div>
            <div class="text-xs text-gray-700 mb-4">
                <p class="mb-1">{{ __('statamic-two-factor::profile.reset.' + languageUserEnforced + '_intro_1') }}</p>
                <p class="mb-1">{{ __('statamic-two-factor::profile.reset.' + languageUserEnforced + '_intro_2') }}</p>
            </div>

            <div>
                <button @click.prevent="confirming = true" class="btn-danger">
                    {{ __('statamic-two-factor::profile.reset.action') }}
                </button>
            </div>
        </div>

        <confirmation-modal
            v-if="confirming"
            :title="__('statamic-two-factor::profile.reset.confirm.title')"
            :danger="true"
            @confirm="action"
            @cancel="confirming = false"
        >
            <p class="mb-2" v-html="__('statamic-two-factor::profile.reset.confirm.'+languageUserEnforced+'_1')"></p>
            <p class="mb-2" v-html="__('statamic-two-factor::profile.reset.confirm.'+languageUserEnforced+'_2')"></p>
            <p class="font-medium text-red-500">
                {{ __('statamic-two-factor::profile.reset.confirm.' + languageUserEnforced + '_3') }}</p>
        </confirmation-modal>
    </div>
</template>

<script>
export default {
    props: {
        enforced: {
            type: Boolean,
            required: true
        },
        languageUser: {
            required: true
        },
        route: {
            required: true
        }
    },

    data() {
        return {
            confirming: false
        }
    },

    computed: {
        languageUserEnforced() {
            return this.languageUser;
        },

        timerId() {
            return 'statamic-two-factor-reset-' + this._uid;
        }
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
                }
            })
                .then(res => res.json())
                .then((data) => {
                    // notify
                    this.$toast.success(__('statamic-two-factor::profile.reset.success'))

                    // emit the update
                    this.$emit('update', 'setup', false);

                    if (data.redirect) {
                        // follow the redirect
                        window.location = data.redirect;
                    }
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
        }
    }
};
</script>
