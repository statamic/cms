<script setup>
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from './Layout.vue';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

const props = defineProps({
    form: Object,
    fieldtypes: Array,
});
</script>

<template>
    <div class="py-4">
        <!-- TOOD: Remove from this component when wiring up the form builder. -->
        <ul>
            <li v-for="fieldtype in fieldtypes" :key="fieldtype.handle">
                {{ fieldtype.title }}
            </li>
        </ul>
    </div>
</template>
