<template>
    <div class="">
        <div class="card p-3 ">
            <header class="mb-3">
                <p class="text-grey" v-text="__('messages.collection_scaffold_instructions')" />
            </header>
             <table class="data-table border rounded">
                <tbody>
                    <tr>
                        <td class="checkbox-column border-r" @click="selected.index = ! selected.index">
                            <div class="flex items-center h-full">
                                <input type="checkbox" v-model="selected.index" class="mr-2" id="field_index" />
                            </div>
                        </td>
                        <td class="border-r">
                            <label for="field_index" v-text="__('Index Template')" />
                        </td>
                        <td :class="{'opacity-25': ! selected.index }">
                            <input type="text" v-model="index" class="input-text font-mono">
                        </td>
                    </tr>
                    <tr>
                        <td class="checkbox-column border-r" @click="selected.show = ! selected.show">
                            <div class="flex items-center h-full">
                                <input type="checkbox" v-model="selected.show" class="mr-2" id="field_template" />
                            </div>
                        </td>
                        <td class="border-r">
                            <label for="field_template" v-text="__('Show Template')" />
                        </td>
                        <td :class="{'opacity-25': ! selected.show }">
                            <input type="text" v-model="show" class="input-text font-mono">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-center mt-4">
            <button tabindex="4" class="btn-primary mx-auto btn-lg" :disabled="! canSubmit" @click="submit">
                {{ __('Create Views')}}
            </button>
        </div>
    </div>
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
                show: true
            }
        }
    },

    computed: {
        canSubmit() {
            return ! _.isEmpty(this.files);
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
        }
    },

    methods: {
        submit() {
            this.$axios.post(this.route, this.files).then(response => {
                window.location = response.data.redirect;
            }).catch(error => {
                this.$toast.error(error.response.data.message);
            });
        }
    },

    mounted() {
        this.$keys.bindGlobal(['return'], e => {
            if (this.canSubmit) {
                this.submit();
            }
        });
    }
}
</script>
