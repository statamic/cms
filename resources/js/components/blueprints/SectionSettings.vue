<template>

    <div class="flex flex-col h-full">

        <div class="flex items-center p-3 bg-grey-20 border-b text-center">
            Section
        </div>

        <div class="flex-1 overflow-scroll">

            <div class="p-2">
                <div class="flex flex-wrap">
                    <div
                        v-for="field in fields"
                        :key="field._id"
                        class="blueprint-section-field"
                    >
                        <div class="blueprint-section-field-inner">
                            <div class="blueprint-drag-handle w-4 border-r"></div>

                            <label class="block">{{ field.display }}</label>
                            <div v-if="field.instructions" class="help-block">{{ field.instructions }}</div>
                            <div class="h-10 border border-dashed rounded bg-grey-20"></div>
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
        section: Object
    },

    computed: {
        fields() {
            return this.section.fields.map(field => {
                const config = field.config || {};

                return {
                    _id: field._id,
                    display: config.display || 'Display',
                    instructions: config.instructions
                };
            });
        }
    }

}
</script>
