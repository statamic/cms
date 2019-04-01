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

            <div class="replicator-set-header">
            <div class="item-move sortable-handle bard-drag-handle"></div>
            <div class="flex-1 ml-1 flex items-center">
                <label v-text="config.display" class="text-xs"/>
                <div
                    v-if="config.instructions"
                    v-html="instructions"
                    class="help-block replicator-set-instructions" />
            </div>
            <div class="replicator-set-controls">
                <toggle-fieldtype name="set-enabled" class="toggle-sm mr-2" @updated="toggleEnabledState" :value="values.enabled" />
                <dropdown-list>
                    <ul class="dropdown-menu">
                        <li class="warning"><a @click.prevent="destroy">{{ __('Delete Set') }}</a></li>
                    </ul>
                </dropdown-list>
            </div>
        </div>

            <div v-show="!isHidden" v-if="fields.length">
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
