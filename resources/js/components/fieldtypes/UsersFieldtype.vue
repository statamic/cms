<template>
    <div class="users-fieldtype">
        <relate-fieldtype :value="value" :name="name" :config="fieldConfig" @updated="update($event)"></relate-fieldtype>
    </div>
</template>

<script>
export default {

    mixins: [Fieldtype],

    computed: {

        fieldConfig() {
            return { ...this.config, type: 'users' }
        }
    }
};
</script>
