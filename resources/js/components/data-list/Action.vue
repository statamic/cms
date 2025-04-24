<template>
    <span>
        <slot :action="action" :select="select" />

        <confirmation-modal
            v-if="confirming"
            :title="action.title"
            :danger="action.dangerous"
            :buttonText="runButtonText"
            :busy="running"
            @confirm="confirm"
            @cancel="reset"
        >
            <div
                v-if="confirmationText"
                v-text="confirmationText"
                :class="{ 'mb-4': warningText || showDirtyWarning || action.fields.length }"
            />

            <div
                v-if="warningText"
                v-text="warningText"
                class="text-red-500"
                :class="{ 'mb-4': showDirtyWarning || action.fields.length }"
            />

            <div
                v-if="showDirtyWarning"
                v-text="dirtyText"
                class="text-red-500"
                :class="{ 'mb-4': action.fields.length }"
            />

            <publish-container
                v-if="action.fields.length"
                name="confirm-action"
                :blueprint="fieldset"
                :values="values"
                :meta="action.meta"
                :errors="errors"
                @updated="values = $event"
                v-slot="{ setFieldValue, setFieldMeta }"
            >
                <publish-fields :fields="action.fields" @updated="setFieldValue" @meta-updated="setFieldMeta" />
            </publish-container>
        </confirmation-modal>
    </span>
</template>

<script>
import PublishFields from '../publish/Fields.vue';
import HasElevatedSession from '@statamic/mixins/HasElevatedSession.js';

export default {
    mixins: [HasElevatedSession],

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
            type: Object,
        },
        isDirty: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            confirming: false,
            fieldset: { tabs: [{ fields: this.action.fields }] },
            values: this.action.values,
            running: false,
        };
    },

    computed: {
        confirmationText() {
            if (!this.action.confirmationText) return;

            return __n(this.action.confirmationText, this.selections);
        },

        warningText() {
            if (!this.action.warningText) return;

            return __n(this.action.warningText, this.selections);
        },

        dirtyText() {
            if (!this.isDirty) return;

            return __(this.action.dirtyWarningText);
        },

        showDirtyWarning() {
            return this.isDirty && this.action.dirtyWarningText && !this.action.bypassesDirtyWarning;
        },

        runButtonText() {
            return __n(this.action.buttonText, this.selections);
        },
    },

    created() {
        this.$events.$on('reset-action-modals', this.reset);
    },

    unmounted() {
        this.$events.$off('reset-action-modals', this.reset);
    },

    methods: {
        onDone() {
            this.running = false;
        },

        select() {
            if (this.action.confirm) {
                this.confirming = true;
                return;
            }

            if (this.action.requiresElevatedSession) {
                this.requireElevatedSession()
                    .then(() => this.performAction())
                    .catch(() => {});
                return;
            }

            this.performAction();
        },

        confirm() {
            if (this.action.requiresElevatedSession) {
                this.requireElevatedSession().then(() => this.performAction());
                return;
            }

            this.performAction();
        },

        performAction() {
            this.running = true;
            this.$emit('selected', this.action, this.values, this.onDone);
        },

        reset() {
            this.confirming = false;

            this.values = clone(this.action.values);
        },
    },
};
</script>
