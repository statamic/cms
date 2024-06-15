<template>
    <div class="select select-full" :data-content="selected[0].toUpperCase() + selected.slice(1)">
        <span :class="`status status-${selected}`"></span>
        <select name="status" v-model="selected">
            <option v-for="option in options" :key="option.value" :value="option.value">
                {{ option.text }}
            </option>
        </select>
    </div>
</template>


<script>
import { ref, computed } from 'vue';

export default {
    props: {
        selected: {
            type: String,
            required: true
        },
        translations: {
            type: Object,
            required: true
        }
    },
    setup(props) {
        const options = ref([
            { text: 'Live', value: 'live' },
            { text: 'Hidden', value: 'hidden' },
            { text: 'Draft', value: 'draft' }
        ]);

        const selected = ref(props.selected);

        const dataContent = computed(() => {
            return selected.value[0].toUpperCase() + selected.value.slice(1);
        });

        const statusClass = computed(() => {
            return `status status-${selected.value}`;
        });

        return {
            options,
            selected,
            dataContent,
            statusClass
        };
    }
};
</script>

