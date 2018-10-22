<template>
    <div>
        <p class="mb-2">
            <template v-if="composer.status">{{ composer.status }}</template>
            <span v-if="polling" class="icon icon-circular-graph animation-spin ml-1"></span>
        </p>
        <pre v-if="output" class="p-1 rounded bg-grey-lighter text-grey text-sm clearfix">{{ output }}</pre>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        data() {
            return {
                output: false,
                polling: false,
            };
        },

        computed: {
            composer() {
                return this.$store.state.statamic.composer;
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
                axios.get('/cp/composer/check').then(response => {
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
                }, 1000);
            },
        }
    }
</script>
