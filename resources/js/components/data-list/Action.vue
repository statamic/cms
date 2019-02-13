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

                    <div v-else class="p-3" v-text="__('Are you sure you want to run this action?')" />
                </div>

                <div class="p-3 bg-grey-lightest border-t flex items-center text-sm">
                    <button class="btn btn-primary" @click="confirm">Run Action</button>
                    <button @click="cancel" class="ml-3 text-grey">Cancel</button>
                </div>
            </div>
        </modal>
    </span>

</template>


<script>
import Fieldset from '../publish/Fieldset';
import PublishFields from '../publish/Fields.vue';

export default {

    components: {
        PublishFields,
    },

    props: {
        action: Object
    },

    data() {
        return {
            confirming: false,
            fieldset: {sections:[{fields:this.action.fields}]},
            values: {},
            errors: {},
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
