<template>
    <div class="dropdown relative" :class="{'open': isOpen}" v-on-clickaway="close">
        <div @click="toggle" aria-haspopup="true" :aria-expanded="isOpen">
            <slot name="trigger">
                <a class="dropdown-icon">
                    <svg class="rotating-dots fill-current" width="12" viewBox="0 0 24 24"><circle cx="3" cy="12" r="3"/><circle cx="12" cy="12" r="3"/><circle cx="21" cy="12" r="3"/></svg>
                </a>
            </slot>
        </div>
        <slot></slot>
    </div>
</template>

<script>
import { mixin as clickaway } from 'vue-clickaway';

export default {
    mixins: [ clickaway ],
    data() {
        return {
            isOpen: false
        }
    },
    methods: {
        toggle() {
            this.isOpen = ! this.isOpen;
        },
        open() {
            this.isOpen = true
        },
        close() {
            this.isOpen = false
        }
    }
}
</script>
