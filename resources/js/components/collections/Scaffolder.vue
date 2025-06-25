<template>
    <ui-panel :heading="__('messages.collection_scaffold_instructions')">
        <table class="data-table">
            <tbody>
                <tr>
                    <td class="w-1/4">
                        <div class="flex items-center gap-3">
                            <ui-switch
                                v-model="selected.index"
                                size="sm"
                                id="field_index"
                            />
                            <label for="field_index" v-text="__('Index Template')" />
                        </div>
                    </td>
                    <td>
                        <ui-input v-model="index" :disabled="!selected.index" />
                    </td>
                </tr>
                <tr>
                    <td class="w-1/4">
                        <div class="flex items-center gap-3">
                            <ui-switch
                                v-model="selected.show"
                                size="sm"
                                id="field_template"
                            />
                            <label for="field_template" v-text="__('Show Template')" />
                        </div>
                    </td>
                    <td>
                        <ui-input v-model="show" :disabled="!selected.show" />
                    </td>
                </tr>
            </tbody>
        </table>
    </ui-panel>

    <ui-button class="float-right" variant="primary" tabindex="4" :disabled="!canSubmit" @click="submit">
        {{ __('Create Views') }}
    </ui-button>

</template>

<script>
export default {
    props: {
        route: { type: String },
        title: { type: String },
        handle: { type: String },
    },

    data() {
        return {
            index: this.handle + '/index',
            show: this.handle + '/show',
            selected: {
                blueprint: true,
                index: true,
                show: true,
            },
        };
    },

    computed: {
        canSubmit() {
            return Object.keys(this.files).length > 0;
        },

        files() {
            var files = {};

            if (this.selected.index) {
                files.index = this.index;
            }

            if (this.selected.show) {
                files.show = this.show;
            }

            return files;
        },
    },

    methods: {
        submit() {
            this.$axios
                .post(this.route, this.files)
                .then((response) => {
                    window.location = response.data.redirect;
                })
                .catch((error) => {
                    this.$toast.error(error.response.data.message);
                });
        },
    },

    mounted() {
        this.$keys.bindGlobal(['return'], (e) => {
            if (this.canSubmit) {
                this.submit();
            }
        });
    },
};
</script>
