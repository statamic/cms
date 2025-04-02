<template>
    <div>
        <div class="mb-8">
            <div class="font-semibold mb-2">{{ __('statamic-two-factor::profile.recovery_codes.title') }}</div>

            <div class="text-xs text-gray-700 mb-4">
                <p class="mb-1">{{ __('statamic-two-factor::profile.recovery_codes.intro') }}</p>
            </div>

            <div class="sm:flex -mt-2">
                <button :disabled="codes" class="btn mt-2 mr-2" @click.prevent="show">{{ __('statamic-two-factor::profile.recovery_codes.show.action') }}</button>
                <button class="btn mt-2" @click.prevent="confirming = true">{{ __('statamic-two-factor::profile.recovery_codes.regenerate.action') }}</button>
            </div>

            <div v-if="codes" class="bg-gray-200 dark:bg-dark-650 inline-block rounded-lg px-4 py-4 mt-6">
                <div class="px-2 text-sm font-medium mb-2">
                    <span v-if="newCodes">{{ __('statamic-two-factor::profile.recovery_codes.codes.new') }}:</span>
                    <span v-else>{{ __('statamic-two-factor::profile.recovery_codes.codes.show') }}:</span>
                </div>
                <div class="font-mono flex flex-wrap text-gray-700">
                    <div v-for="(code, index) in codes" :key="code" class="px-2">{{ code }}</div>
                </div>
                <div v-if="newCodes"
                     class="text-sm mt-2 px-2 text-red-500">
                    {{ __('statamic-two-factor::profile.recovery_codes.codes.footnote') }}
                </div>
            </div>
        </div>

        <confirmation-modal
            v-if="confirming"
            :danger="true"
            :title="__('statamic-two-factor::profile.recovery_codes.regenerate.confirm.title')"
            @cancel="confirming = false"
            @confirm="regenerate"
        >
            <p class="mb-2">{{ __('statamic-two-factor::profile.recovery_codes.regenerate.confirm.body_1') }}</p>
            <p>{{ __('statamic-two-factor::profile.recovery_codes.regenerate.confirm.body_2') }}</p>
        </confirmation-modal>
    </div>
</template>

<script>
export default {
    props: {
        routes: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            codes: null,
            confirming: false,
            newCodes: false
        }
    },

    computed: {
        timerId() {
            return 'statamic-two-factor-recovery-codes-' + this._uid;
        }
    },

    methods: {
        regenerate() {
            // we must have a route
            if (!this.routes.generate) {
                return;
            }

            // clear codes
            this.codes = null;

            // close dialog
            this.confirming = false;

            // start the progress timer
            this.$progress.start(this.timerId);

            fetch(this.routes.generate, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': Statamic.$config.get('csrfToken'),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
                .then(res => res.json())
                .then((data) => {
                    // update codes
                    this.codes = data.recovery_codes;

                    // mark as re-generated
                    this.newCodes = true;
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

        show() {
            // we must have a route
            if (!this.routes.show) {
                return;
            }

            // clear codes
            this.codes = null;

            // start the progress timer
            this.$progress.start(this.timerId);

            fetch(this.routes.show, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
                .then(res => res.json())
                .then((data) => {
                    // update codes
                    this.codes = data.recovery_codes
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
