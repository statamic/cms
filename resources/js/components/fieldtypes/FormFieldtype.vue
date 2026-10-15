<template>
    <div>
        <RelationshipFieldtype
            v-bind="$props"
            @update:value="$emit('update:value', $event)"
            @update:meta="$emit('update:meta', $event)"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
        />

        <SubmissionsStack
            v-if="submissionsMeta"
            v-model:open="viewingSubmissions"
            :form="submissionsMeta.form"
            :action-url="submissionsMeta.actionUrl"
            :filters="submissionsMeta.filters"
        />
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import RelationshipFieldtype from './relationship/RelationshipFieldtype.vue';
import SubmissionsStack from '@/components/forms/SubmissionsStack.vue';

export default {
    mixins: [Fieldtype],

    components: {
        RelationshipFieldtype,
        SubmissionsStack,
    },

    provide() {
        return {
            formFieldtypeItem: {
                hasSubmissions: (item) => this.submissionsMeta?.form === item.id,
                hasConfigure: () => false,
                viewSubmissions: () => (this.viewingSubmissions = true),
                configure: () => {},
            },
        };
    },

    data() {
        return {
            viewingSubmissions: false,
        };
    },

    computed: {
        forms() {
            return this.value ?? [];
        },

        submissionsMeta() {
            const submissions = this.meta.submissions;

            if (!submissions || !this.forms.includes(submissions.form)) return null;

            return submissions;
        },

        replicatorPreview() {
            if (!this.showFieldPreviews) return;

            return this.forms.map((id) => {
                const item = this.meta.data.find((d) => d.id === id);
                return item ? item.title : id;
            });
        },

        internalFieldActions() {
            return [
                {
                    title: __('Unlink All'),
                    dangerous: true,
                    run: this.unlinkAll,
                    visible: this.forms.length > 0,
                },
            ];
        },
    },

    methods: {
        unlinkAll() {
            this.update([]);
            this.updateMeta({ ...this.meta, data: [] });
        },
    },
};
</script>
