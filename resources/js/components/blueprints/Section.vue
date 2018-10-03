<template>

    <div class="blueprint-section">
        <div class="blueprint-section-card card p-0 h-full flex flex-col">

            <div class="bg-grey-lightest border-b text-sm flex rounded-t;">
                <div class="blueprint-drag-handle blueprint-section-drag-handle w-4 border-r"></div>
                <div class="px-2 py-1 flex-1">
                    <span class="font-medium mr-1">{{ section.display }}</span>
                </div>
                <div class="flex items-center px-1">
                    <button class="opacity-50 hover:opacity-100 mr-1"><span class="icon icon-cog" /></button>
                    <button @click.prevent="$emit('deleted')" class="opacity-50 hover:opacity-100"><span class="icon icon-cross" /></button>
                </div>
            </div>

            <div class="p-2 flex flex-col flex-1">

                <div class="blueprint-section-draggable-zone flex-1" :class="{ 'mb-2': section.fields.length > 0 }">
                    <blueprint-field
                        v-for="field in section.fields"
                        :key="field._id"
                        :field="field"
                    />
                </div>

                <div>
                    <button class="btn btn-default btn-small">+ {{ __('Add Field') }}</button>
                </div>

            </div>

        </div>
    </div>

</template>

<script>
import BlueprintField from './Field.vue';

export default {

    components: {
        BlueprintField
    },

    props: {
        section: {
            type: Object,
            required: true
        }
    }

}
</script>
