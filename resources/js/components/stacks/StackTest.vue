<template>

    <div>
        <small class="text-grey">Collections > Blog ></small>
        <h1 class="mt-1 mb-3">Fire!</h1>

        {{ depth === 0 ? 'Base' : `Stack ${depth}` }}<br><br>

        <button @click="show = true" class="btn">Open Stack {{ depth + 1 }}</button>

        <stack name="test" v-if="show" @closed="show = false">
            <div slot-scope="{ depth }" class="p-4">
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
            show: false
        }
    }

}
</script>
