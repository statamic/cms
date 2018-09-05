<template>

    <div class="bg-grey-lightest shadow mb-2 rounded border">

        <div class="cursor-move bg-grey-lighter border-b px-2 py-1 text-sm flex items-center justify-between">
            <div class="pt-1">
                <label class="mb-1" v-text="config.display" />
                <div
                    v-if="config.instructions"
                    v-html="instructions"
                    class="help-block -mt-1" />
            </div>
            <div>
                <dropdown-list>
                    <li><a @click.prevent="">Collapse All</a></li>
                    <li><a @click.prevent="">Expand All</a></li>
                    <li class="divider"></li>
                    <li><a @click.prevent="">Inline insert for each set here</a></li>
                    <li class="warning"><a @click.prevent="$emit('removed', index)">Delete Set</a></li>
                </dropdown-list>
            </div>
        </div>

        <div>
            <set-field
                v-for="field in fields"
                :key="field.handle"
                :field="field"
                :value="values[field.handle]"
                @updated="updated"
            />
        </div>

    </div>

</template>

<script>
import SetField from './Field.vue';

export default {

    components: { SetField },

    props: {
        config: {
            type: Object,
            required: true
        },
        index: {
            type: Number,
            required: true
        },
        values: {
            type: Object,
            required: true
        }
    },

    computed: {

        fields() {
            return this.config.fields;
        },

        instructions() {
            return markdown(this.config.instructions);
        }

    },

    methods: {

        updated(handle, value) {
            let set = JSON.parse(JSON.stringify(this.values));
            set[handle] = value;
            this.$emit('updated', this.index, set);
        }

    }

}
</script>
