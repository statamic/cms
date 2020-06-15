<template>

    <span>
        <slot :action="action" :select="select" />

        <confirmation-modal
            v-if="confirming"
            :title="action.title"
            :danger="action.dangerous"
            :buttonText="runButtonText"
            @confirm="confirm"
            @cancel="cancel"
        >
            <div v-if="confirmationText" v-text="confirmationText" :class="{ 'mb-2': action.fields.length }" />

            <publish-container
                v-if="action.fields.length"
                name="confirm-action"
                :blueprint="fieldset"
                :values="values"
                :meta="action.meta"
                :errors="errors"
                @updated="values = $event"
            >
                <publish-fields
                    slot-scope="{ setFieldValue, setFieldMeta }"
                    :fields="action.fields"
                    @updated="setFieldValue"
                    @meta-updated="setFieldMeta"
                />
            </publish-container>
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
            values: this.action.values,
            errors: {},
        }
    },

    computed: {

        confirmationText() {
            if (! this.action.confirmationText) return;

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
        }
    }

}
</script>
