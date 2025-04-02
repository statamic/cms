<template>
    <div>
        <div class="mb-8">
            <div class="mb-2 font-semibold">{{ __('Recovery codes') }}</div>

            <div class="mb-4 text-xs text-gray-700">
                <p class="mb-1">{{ __('statamic::messages.two_factor_recovery_codes_introduction') }}</p>
            </div>

            <div class="-mt-2 sm:flex">
                <button :disabled="codes" class="btn mr-2 mt-2" @click.prevent="show">
                    {{ __('Show recovery codes') }}
                </button>
                <button class="btn mt-2" @click.prevent="confirming = true">
                    {{ __('Create new recovery codes') }}
                </button>
            </div>

            <div v-if="codes" class="mt-6 inline-block rounded-lg bg-gray-200 px-4 py-4 dark:bg-dark-650">
                <div class="mb-2 px-2 text-sm font-medium">
                    <span v-if="newCodes">{{ __('Your new recovery codes') }}:</span>
                    <span v-else>{{ __('Your recovery codes') }}:</span>
                </div>
                <div class="flex flex-wrap font-mono text-gray-700">
                    <div v-for="(code, index) in codes" :key="code" class="px-2">{{ code }}</div>
                </div>
                <div v-if="newCodes" class="mt-2 px-2 text-sm text-red-500">
                    {{ __('statamic::messages.two_factor_recovery_codes_footnote') }}
                </div>
            </div>
        </div>

        <confirmation-modal
            v-if="confirming"
            :danger="true"
            :title="__('Are you sure?')"
            @cancel="confirming = false"
            @confirm="regenerate"
        >
            <p class="mb-2">{{ __('statamic::messages.two_factor_regenerate_recovery_codes_1') }}</p>
            <p>{{ __('statamic::messages.two_factor_regenerate_recovery_codes_2') }}</p>
        </confirmation-modal>
    </div>
</template>

<script>
export default {
    props: {
        routes: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            codes: null,
            confirming: false,
            newCodes: false,
        };
    },

    computed: {
        timerId() {
            return 'statamic-two-factor-recovery-codes-' + this._uid;
        },
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
                },
            })
                .then((res) => res.json())
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
                },
            })
                .then((res) => res.json())
                .then((data) => {
                    // update codes
                    this.codes = data.recovery_codes;
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
