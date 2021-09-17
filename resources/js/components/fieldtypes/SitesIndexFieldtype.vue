<template>

    <div>
        {{ sites }}
    </div>

</template>

<script>
export default {
    mixins: [IndexFieldtype],

    computed: {
        sites() {
            return this.value.map(site => site.title).join(', ');
        }
    }
}
</script>
