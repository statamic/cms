<template>
    <div class="quick-list">
        <div class="quick-list-content">
            <slot />
        </div>
        <dropdown-list placement="left-start" :offset="[7, -3]">
            <slot name="dropdown" />
        </dropdown-list>
    </div>
</template>

<script>
export default {

}
</script>
