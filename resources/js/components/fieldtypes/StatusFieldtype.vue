<template>
<div class="select select-full" :data-content="selected[0].toUpperCase() + selected.slice(1)">
	<span :class="`status status-${selected}`"></span>
	<select name="status" v-model="selected" options="options"></select>
</div>
</template>

<script>
export default {

    props: ['selected', 'translations'],

    data: function () {
        return {
            options: [
                {text: 'Live', value: 'live'},
                {text: 'Hidden', value: 'hidden'},
                {text: 'Draft', value: 'draft'}
            ]
        }
    }
}
</script>
