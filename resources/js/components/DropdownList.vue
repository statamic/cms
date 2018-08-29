<template>
    <div class="dropdown">
        <slot name="trigger">
            <a class="dropdown-toggle text-grey" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <svg class="rotating-dots fill-current" width="12" viewBox="0 0 24 24"><circle cx="3" cy="12" r="3"/><circle cx="12" cy="12" r="3"/><circle cx="21" cy="12" r="3"/></svg>
            </a>
        </slot>
        <ul class="dropdown-menu">
            <slot></slot>
        </ul>
    </div>
</template>

<script>
export default { }
</script>
