<template>

    <span>
        <slot :action="action" :select="select" />

        <confirmation-modal
            v-if="confirming"
            :title="action.title"
            :danger="action.dangerous"
            :buttonText="runButtonText"
            @confirm="confirm"
            @cancel="reset"
        >
            <div v-if="confirmationText" v-text="confirmationText" :class="{ 'mb-2': warningText || action.fields.length }" />

            <div v-if="warningText" v-text="warningText" class="text-red" :class="{ 'mb-2': action.fields.length }" />

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
        },
        errors: {
            type: Object
        }
    },

    data() {
        return {
            confirming: false,
            fieldset: {sections:[{fields:this.action.fields}]},
            values: this.action.values,
        }
    },

    computed: {

        confirmationText() {
            if (! this.action.confirmationText) return;

            return __n(this.action.confirmationText, this.selections);
        },

        warningText() {
            if (! this.action.warningText) return;

            return __n(this.action.warningText, this.selections);
        },

        runButtonText() {
            return __n(this.action.buttonText, this.selections);
        }

    },

    created() {
        this.$events.$on('reset-action-modals', this.reset);
    },

    methods: {

        select() {
            if (this.action.confirm) {
                this.confirming = true;
                return;
            }

            this.$emit('selected', this.action, this.values);
        },

        confirm() {
            this.$emit('selected', this.action, this.values);
        },

        reset() {
            this.confirming = false;

            this.values = clone(this.action.values);
        },

    }

}
</script>
