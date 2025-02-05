<template>
    <div class="flex h-full flex-col">
        <div class="flex items-center border-b bg-gray-200 p-6 text-center">
            {{ __('Tabs') }}
        </div>

        <div class="flex-1 overflow-scroll">
            <div class="p-4">
                <div class="flex flex-wrap">
                    <div v-for="field in fields" :key="field._id" class="blueprint-tab-field">
                        <div class="blueprint-tab-field-inner">
                            <div class="blueprint-drag-handle w-4 ltr:border-r rtl:border-l"></div>

                            <label class="block">{{ field.display }}</label>
                            <div v-if="field.instructions" class="help-block">{{ field.instructions }}</div>
                            <div class="h-10 rounded border border-dashed bg-gray-200"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import PublishField from '../publish/Field.vue';

export default {
    components: { PublishField },

    props: {
        tab: Object,
    },

    computed: {
        fields() {
            return this.tab.fields.map((field) => {
                const config = field.config || {};

                return {
                    _id: field._id,
                    display: config.display || 'Display',
                    instructions: config.instructions,
                };
            });
        },
    },
};
</script>
