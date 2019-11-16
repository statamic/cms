export default {

    data() {
        return {
            previews: {},
        }
    },

    computed: {
        previewText() {
            return Object.values(this.previews)
                .filter(value => {
                    if (['null', '[]', '{}', ''].includes(JSON.stringify(value))) return null;
                    return value;
                })
                .map(value => {
                    if (typeof value === 'string') return value;

                    if (Array.isArray(value) && typeof value[0] === 'string') {
                        return value.join(', ');
                    }

                    return JSON.stringify(value);
                })
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