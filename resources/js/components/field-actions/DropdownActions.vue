<template>
    <div>
        <button
            v-for="action in actions"
            @click="run(action)"
            :class="{ warning: action.dangerous }"
            v-text="action.title" />
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
