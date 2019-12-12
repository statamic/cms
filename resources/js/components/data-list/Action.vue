<template>

    <span>
        <slot :action="action" :select="select" />

        <confirmation-modal
            v-if="confirming"
            :title="action.title"
            :danger="action.dangerous"
            :buttonText="runButtonText"
            :bodyText="confirmationText"
            @confirm="confirm"
            @cancel="cancel"
        >
            <publish-container
                v-if="action.fields.length"
                name="confirm-action"
                :blueprint="fieldset"
                :values="values"
                :meta="action.meta"
                :errors="errors"
            >
                <publish-fields :fields="action.fields" @updated="valueUpdated" />
            </publish-container>

            <div v-else v-text="confirmationText" />
        </confirmation-modal>
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
            return __n(this.action.confirmationText, this.selections);
        },

        runButtonText() {
            return __n(this.action.buttonText, this.selections);
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

        valueUpdated(field, value) {
            this.values[field] = value;
        }
    }

}
</script>
