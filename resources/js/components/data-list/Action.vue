<template>

    <span>
        <slot :action="action" :select="select" />

        <modal v-if="confirming" name="confirm-action" :pivotY="0.1">
            <div class="flex flex-col h-full">
                <div class="text-lg font-medium p-3 pb-0">
                    {{ action.title }}
                </div>
                <div class="flex-1 overflow-scrollf">
                    <publish-container
                        v-if="action.fields.length"
                        name="confirm-action"
                        :fieldset="fieldset"
                        :values="values"
                        :meta="action.meta"
                        :errors="errors"
                        @updated="valuesUpdated"
                    >
                        <publish-fields :fields="action.fields" />
                    </publish-container>

                    <div v-else class="p-3" v-text="confirmationText" />
                </div>

                <div class="p-3 bg-grey-20 border-t flex items-center text-sm">
                    <button class="btn" :class="[ action.dangerous ? 'btn-danger' : 'btn-primary' ]" @click="confirm" v-text="runButtonText" />
                    <button @click="cancel" class="ml-3 text-grey">Cancel</button>
                </div>
            </div>
        </modal>
    </span>

</template>


<script>
import PublishFields from '../publish/Fields.vue';

export default {

    components: {
        PublishFields,
    },

    props: {
        action: {
            type: Object,
            required: true,
        },
        selections: {
            type: Number,
            required: true,
        }
    },

    data() {
        return {
            confirming: false,
            fieldset: {sections:[{fields:this.action.fields}]},
            values: {},
            errors: {},
        }
    },

    computed: {

        confirmationText() {
            return this.selections === 1
                ? __('Are you sure you want to run this action?')
                : __n('Are you sure you want to run this action on :count items?', this.selections);
        },

        runButtonText() {
            return this.selections === 1
                ? __('Run action')
                : __n('Run action on :count items', this.selections);
        }

    },

    methods: {

        select() {
            this.confirming = true;
        },

        confirm() {
            this.$emit('selected', this.action, this.values);
            this.confirming = false;
        },

        cancel() {
            this.confirming = false;
        },

        valuesUpdated(values) {
            this.values = Object.assign({}, values);
        }
    }

}
</script>
