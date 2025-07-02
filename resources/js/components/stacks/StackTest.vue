<template>
    <div>
        <small class="text-gray">Collections > Blog ></small>
        <h1 class="mt-2 mb-6">Fire!</h1>

        {{ depth === 0 ? 'Base' : `Stack ${depth}` }}<br /><br />

        <button @click="show = true" class="btn">Open Stack {{ depth + 1 }}</button>

        <stack name="test" v-if="show" @closed="show = false" v-slot="{ depth }">
            <div class="p-8">
                <stack-test :depth="depth" />
            </div>
        </stack>
    </div>
</template>

<script>
export default {
    props: ['depth'],

    data() {
        return {
            show: false,
        };
    },
};
</script>
