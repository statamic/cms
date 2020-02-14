<template>
    <div class="template-fieldtype-wrapper">

        <div v-if="loading" class="loading loading-basic">
            <span class="icon icon-circular-graph animation-spin"></span> {{ __('Loading') }}
        </div>

        <div v-if="!loading">

            <div class="flex" v-if="creating">
                <input type="text"
                       ref="createField"
                       class="input-text flex-1"
                       v-model="newFieldsetName"
                       @keydown.enter.prevent="create"
                       @keydown.esc="cancelAdd"
                />
                <div class="flex">
                    <button class="btn-primary ml-1" @click.prevent="create" :disabled="storePending">{{ __('Create') }}</button>
                    <button class="btn-default ml-1" @click.prevent="cancelAdd">{{ __('Cancel') }}</button>
                </div>
            </div>

            <div class="flex" v-if="!creating">
                <div class="flex-1">
                    <select-fieldtype
                        :handle="handle"
                        :value="value"
                        :config="selectConfig"
                        @input="update" />
                </div>

                <div class="flex">
                    <button class="btn ml-1" @click.prevent="add" v-if="canAdd">
                        <span class="icon icon-plus"></span>
                    </button>
                    <button class="btn ml-1" @click.prevent="refresh">
                        <span class="icon icon-cycle"></span>
                    </button>
                </div>
            </div>

        </div>

    </div>
</template>

<script>
export default {

    mixins: [Fieldtype],

    props: {
        required: Boolean,
    },

    data() {
        return {
            loading: true,
            creating: false,
            storePending: false,
            newFieldsetName: '',
            options: {}
        }
    },

    computed: {
        selectConfig: function() {
            return {
                options: this.options
            };
        },

        canAdd() {
            return true;
            // return Vue.can('super'); // TODO
        }
    },

    mounted() {
        this.getFieldsets();
    },

    methods: {

        add() {
            this.creating = true;
            this.$nextTick(() => this.$refs.createField.focus());
        },

        cancelAdd() {
            this.creating = false;
            this.newFieldsetName = '';
        },

        create() {
            if (this.storePending) return;

            this.storePending = true;

            this.$axios.post(cp_url('fieldsets/quick'), { title: this.newFieldsetName }).then(response => {
                this.update(this.newFieldsetName);
                this.storePending = false;
                this.cancelAdd();
                this.refresh();
                this.$toast.success(__('Fieldset created'));
            });
        },

        refresh() {
            this.loading = true;
            this.getFieldsets();
        },

        getFieldsets() {
            this.$axios.get(cp_url('fieldsets')).then(response => {
                this.options = response.data.map(fieldset => {
                    return {
                        value: fieldset.id,
                        text: fieldset.title
                    };
                });

                this.loading = false;
            });
        }

    }
};
</script>
