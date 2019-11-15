export default {

    data() {
        return {
            previews: {},
        }
    },

    computed: {
        previewText() {
            return Object.values(this.previews)
                .filter(value => !!value)
                .join(' / ');
        }
    },

    methods: {
        initPreviews() {
            let previews = {};
            this.fields.forEach(field => previews[field.handle] = null);
            this.previews = previews;
        }
    }

}