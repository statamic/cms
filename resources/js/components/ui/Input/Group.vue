<script setup>
const props = defineProps({
    label: { type: String, default: null },
    description: { type: String, default: null },
    required: { type: Boolean, default: false },
    badge: { type: String, default: null },
});

</script>

<template>
    <ui-with-field :label :description :required :badge>
        <div
            class="
                flex group/input w-full *:data-ui-input:grow
                [&>[data-ui-input]:not(:first-child):not(:last-child)>[data-ui-group-target]:not([data-invalid])]:border-x-0
                [&>[data-ui-input]:first-child:not(:last-child)>[data-ui-group-target]:not([data-invalid])]:border-e-0
                [&>[data-ui-input]:last-child:not(:first-child)>[data-ui-group-target]:not([data-invalid])]:border-s-0

                [&>[data-ui-group-target]:not(:first-child):not(:last-child)]:rounded-none
                [&>[data-ui-group-target]:first-child:not(:last-child)]:rounded-e-none
                [&>[data-ui-group-target]:last-child:not(:first-child)]:rounded-s-none
                [&>*:not(:first-child):not(:last-child):not(:only-child)_[data-ui-group-target]]:rounded-none
                [&>*:first-child:not(:last-child)_[data-ui-group-target]]:rounded-e-none
                [&>*:last-child:not(:first-child)_[data-ui-group-target]]:rounded-s-none
            "
            data-ui-input-group
        >
            <slot />
        </div>
    </ui-with-field>
</template>
