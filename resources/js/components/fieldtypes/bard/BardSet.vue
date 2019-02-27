<template>

    <div class="bard-block bard-set" :class="{'bard-set-solo': goingSolo}">

        <slot name="divider-start"></slot>

        <div
            :class="{
                'collapsed': isHidden,
                'bg-white shadow mb-2 rounded border': !goingSolo
            }"
            @dblclick="toggle"
        >
            <div
                v-if="!goingSolo"
                class="bard-drag-handle cursor-move bg-grey-30 border-b px-2 py-1 text-sm flex items-center justify-between"
            >
                <div class="pt-1">
                    <label class="mb-1 leading-none" v-text="display" @click="toggle" />
                    <div
                        v-if="config.instructions"
                        v-html="instructions"
                        class="help-block -mt-1" />
                </div>
                <div>
                    <dropdown-list>
                        <ul class="dropdown-menu">
                            <li class="warning"><a @click.prevent="destroy">Delete Set</a></li>
                        </ul>
                    </dropdown-list>
                </div>
            </div>

            <div
                v-show="!isHidden || goingSolo"
                v-if="fields.length"
            >
                <div class="publish-fields">
                    <set-field
                        v-for="field in fields"
                        :key="field.handle"
                        :field="field"
                        :value="values[field.handle]"
                        :parent-name="parentName"
                        :set-index="index"
                        :class="{ 'bard-drag-handle': goingSolo }"
                        @updated="updated"
                    />
                </div>
            </div>

        </div>

        <slot name="divider-end"></slot>

    </div>

</template>

<script>
import ReplicatorSet from '../replicator/Set.vue';

export default {

    mixins: [ReplicatorSet],

    methods: {
        focusAt(position) {
            this.focus();
        },
    },

    computed: {
        goingSolo() {
            const firstFieldtype = _.first(this.config.fields).type;
            const supportedFieldtypes = ['assets'];

            return this.config.fields.length === 1
                && _.contains(supportedFieldtypes, firstFieldtype);
        }
    },

    events: {
        'asset-field.delete-bard-set': function () {
            this.$emit('deleted', this.index);
        }
    }

}
</script>
