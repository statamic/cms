<template>
    <div>
        <publish-form
            :title="title"
            :blueprint="blueprint"
            :meta="meta"
            :values="values"
            read-only
        ></publish-form>
    </div>
</template>

<script>
export default {
    props: {
        date: String,
        blueprint: Object,
        meta: Object,
        values: Object,
    },

    computed: {
        title() {
            let date = new Date(this.date);

            return (
                date.toLocaleDateString(navigator.language, { year: 'numeric', month: 'numeric', day: 'numeric' }) +
                ' ' +
                date.toLocaleTimeString(navigator.language, { hour: 'numeric', minute: 'numeric' })
            );
        },
    }
}
</script>
