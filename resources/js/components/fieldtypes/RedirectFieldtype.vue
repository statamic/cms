<template>
    <div class="flex items-center">
        <div class="mr-2" v-if="!config.required">
            <toggle-fieldtype handle="enabled" v-model="enabled" />
        </div>
        <div class="w-32 mr-2">
            <v-select
                v-if="enabled"
                v-model="option"
                :options="options"
                :clearable="false"
                :reduce="(option) => option.value"
            />
        </div>
        <div class="flex-1">
            <text-input
                v-if="enabled && option === 'url'"
                :value="value"
                @input="update($event)" />
        </div>
    </div>
</template>

<script>
export default {

    mixins: [Fieldtype],

    data() {
        return {
            enabled: this.value != null,
            option: 'url',
            options: [
                {label: 'URL', value: 'url'},
                {label: 'First Child', value: 'first-child'},
            ]
        }
    },

    watch: {
        option(option, oldOption) {
            if (option === 'first-child') {
                this.update('@child');
            }
        },

        enabled(enabled) {
            if (enabled) {
                this.option = 'url';
            } else {
                this.option = null;
                this.update(null);
            }
        }
    },

    created() {
        if (this.value === '@child') {
            this.option = 'first-child';
        }

        if (this.config.required) {
            this.enabled = true;
        }
    }

}
</script>
