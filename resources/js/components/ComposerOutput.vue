<template>
    <div>
        <p class="font-bold flex items-center justify-between">
            <template v-if="composer.status">{{ composer.status }}</template>
            <loading-graphic v-if="polling" text="" class="h-6 w-6"/>
        </p>
        <div class="mt-2 p-2 rounded text-sm font-mono bg-black text-white">
            <div
                ref="output"
                class="whitespace-pre-wrap h-96 overflow-auto" v-html="coloredOutput" />
        </div>
    </div>
</template>

<script>
    const ansi = require('ansi-to-html');

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

        watch: {
            polling(polling) {
                this.$progress.loading('composer-installing', polling);
            },

            output() {
                this.$nextTick(() => {
                    const div = this.$refs.output;
                    div.scrollTop = div.scrollHeight - div.clientHeight;
                });
            }
        },

        computed: {
            composer() {
                return this.$store.state.statamic.composer;
            },

            params() {
                return {package: this.package};
            },

            coloredOutput() {
                return new ansi({
                    fg: "#c7c7c7",
                    bg: "#000000",
                    colors: [
                        '#000000', '#c91b00', '#00c200', '#c7c400', '#0225c7', '#c930c7', '#00c5c7', '#c7c7c7',
                        '#676767', '#ff6d67', '#5ff967', '#fefb67', '#6871ff', '#ff76ff', '#5ffdff', '#feffff',
                    ]
                }).toHtml(this.output || '');
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
                this.$axios.get(cp_url('composer/check'), {params: this.params}).then(response => {
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
