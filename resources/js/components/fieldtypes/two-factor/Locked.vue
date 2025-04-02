<template>
    <div>
        <div class="mb-8">
            <div class="font-semibold mb-2">{{ __('statamic-two-factor::profile.locked.title') }}</div>

            <div class="text-xs text-gray-700 mb-4">
                <p class="mb-1">{{ __('statamic-two-factor::profile.locked.intro') }}</p>
            </div>

            <div class="flex space-x-2">
                <button @click.prevent="confirming = true"
                        class="btn">{{ __('statamic-two-factor::profile.locked.unlock') }}</button>
            </div>
        </div>

        <confirmation-modal
            v-if="confirming"
            :title="__('statamic-two-factor::profile.locked.confirm_title')"
            :danger="true"
            @confirm="action"
            @cancel="confirming = false"
        >
            <p class="mb-2" v-html="__('statamic-two-factor::profile.locked.confirm_1')"></p>
            <p class="mb-2">{{ __('statamic-two-factor::profile.locked.confirm_2') }}</p>
            <p class="font-medium text-red-500">{{ __('statamic-two-factor::profile.locked.confirm_3') }}</p>
        </confirmation-modal>
    </div>
</template>

<script>
export default {
    props: {
        route: {
            type: String,
            required: true
        }
    },

    data() {
        return {
            confirming: false
        }
    },

    computed: {
        timerId() {
            return 'statamic-two-factor-locked-' + this._uid;
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
                },
                //body: new FormData(this.form)
            })
                .then(res => res.json())
                .then((data) => {
                    // notify
                    this.$toast.success(__('statamic-two-factor::profile.locked.success'))

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
        }
    }
};
</script>
