<template>
    <div>
        <text-input
            v-if="this.isInsideFormSubmission"
            type="text"
            :name="name"
            :value="value"
            isReadOnly="true"
        />
        <input
            v-else
            type="hidden"
            :name="name"
            :value="value"
        />
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],

    inject: {
        isInsideFormSubmission: { default: false },
    },
};
</script>
