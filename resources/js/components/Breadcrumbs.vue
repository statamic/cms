<template>

    <div class="subhead">

        <span v-for="(crumb, i) in crumbs" :key="i">
            <a :href="crumb.url"
                v-text="crumb.text"
                class="font-bold hover:text-blue"
            /><span v-if="i < crumbs.length-1" class="px-sm">›</span>
        </span>

    </div>

</template>

<script>
export default {

    props: {
        crumbs: Array
    }

}
</script>
