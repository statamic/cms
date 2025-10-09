<template>
    <div
        :class="[
            'group/button flex flex-wrap justify-center gap-1 lg:gap-x-0',
            'lg:[&>[data-ui-group-target]:not(:first-child):not(:last-child)]:rounded-none',
            'lg:[&>[data-ui-group-target]:first-child:not(:last-child)]:rounded-e-none',
            'lg:[&>[data-ui-group-target]:last-child:not(:first-child)]:rounded-s-none',
            'lg:[&>*:not(:first-child):not(:last-child):not(:only-child)_[data-ui-group-target]]:rounded-none',
            'lg:[&>*:first-child:not(:last-child)_[data-ui-group-target]]:rounded-e-none',
            'lg:[&>*:last-child:not(:first-child)_[data-ui-group-target]]:rounded-s-none',
            'dark:[&_button]:ring-0',
        ]"
        data-ui-button-group
    >
        <slot />
    </div>
</template>
