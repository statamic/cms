<template>
    <div>
        <p class="mb-2">
            <template v-if="composer.status">{{ composer.status }}</template>
            <span v-if="polling" class="icon icon-circular-graph animation-spin ml-1"></span>
        </p>
        <pre v-if="output" class="p-1 rounded bg-grey-30 text-grey text-sm clearfix">{{ output }}</pre>
    </div>
</template>

<script>
    export default {
        props: {
            package: {
                type: String,
                required: true
            }
        },

        data() {
            return {
                output: false,
                polling: false,
            };
        },

        computed: {
            composer() {
                return this.$store.state.statamic.composer;
            },

            params() {
                return {package: this.package};
            }
        },

        created() {
            this.$events.$on('start-composer', this.startComposer);
        },

        destroyed() {
            this.$events.$off('start-composer');
        },

        methods: {
            startComposer() {
                this.output = false;
                this.polling = true;

                this.composerProcess = window.setInterval(() => {
                    this.checkComposer();
                }, 1000);
            },

            checkComposer() {
                this.$axios.get('/cp/composer/check', {params: this.params}).then(response => {
                    this.output = response.data.output;

                    if (response.data.output === false || response.data.completed) {
                        return this.stopComposer();
                    }
                });
            },

            stopComposer() {
                window.clearInterval(this.composerProcess);

                window.setTimeout(() => {
                    this.polling = false;
                    this.$events.$emit('composer-finished');
                }, 1500);
            },
        }
    }
</script>
