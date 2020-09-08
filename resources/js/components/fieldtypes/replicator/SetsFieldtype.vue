<template>

    <div>

        <sections
            :initial-sections="sections"
            :require-section="config.require_section"
            :can-define-localizable="false"
            :add-section-text="__('Add Set')"
            :new-section-text="__('New Set')"
            @updated="sectionsUpdated"
        />

    </div>

</template>

<script>
import Sections from '../../blueprints/Sections.vue';

export default {

    mixins: [Fieldtype],

    components: {
        Sections
    },

    data() {
        return {
            sections: this.value
        }
    },

    methods: {

        sectionsUpdated(sections) {
            this.sections = sections;
        }

    }

}
</script>
