<template>

    <div
        class="blueprint-tab card px-5 py-2 mr-2 cursor-pointer flex"
        :class="{ 'bg-blue-100': currentTab === tab._id }"
        @click="$emit('selected')"
        @mouseenter="$emit('mouseenter')"
    >
        {{ tab.display }}
        <div @click="$emit('removed')">
            <svg-icon name="delete-x" class="w-2 h-2 ml-2 text-gray-600 hover:text-gray-800" />
        </div>
    </div>

</template>

<script>
export default {

    props: {
        tab: {
            type: Object,
            required: true,
        },
        currentTab: {
            type: String,
            required: true,
        },
    }

}
</script>
