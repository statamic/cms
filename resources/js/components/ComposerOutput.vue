<template>
    <div>
        <span v-if="polling" class="icon icon-circular-graph animation-spin float-right"></span>
        <slot></slot>
        <pre v-if="output" class="p-1 rounded bg-grey-lighter text-grey text-sm">{{ output }}</pre>
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
                this.polling = false;

                window.clearInterval(this.composerProcess);

                this.$events.$emit('composer-finished');
            },
        }
    }
</script>
