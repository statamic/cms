<template>
    <div>
        <button
            v-for="tool in tools"
            @click="run(tool)"
        >
            {{ tool.display }}
        </button>
    </div>
</template>

<script>
export default {

    props: ['tools'],

    inject: ['popover'],

    methods: {

        run(tool) {
            this.$emit('run', tool);

            this.popover.vm.close();
        },

    }

}
</script>
