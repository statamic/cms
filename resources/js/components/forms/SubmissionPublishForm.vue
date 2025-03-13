<template>
    <div>
        <publish-form :title="title" :blueprint="blueprint" :meta="meta" :values="values" read-only></publish-form>
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
            return Intl.DateTimeFormat(navigator.language, { dateStyle: 'short', timeStyle: 'short' }).format(
                new Date(this.date),
            );
        },
    },
};
</script>
