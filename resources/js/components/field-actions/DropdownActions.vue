<template>
    <div>
        <button
            v-for="action in actions"
            @click="run(action)"
        >
            {{ action.display || action.title }}
        </button>
    </div>
</template>

<script>
export default {

    props: ['actions'],

    inject: ['popover'],

    methods: {

        run(action) {
            action.run();

            this.popover.vm.close();
        },

    }

}
</script>
