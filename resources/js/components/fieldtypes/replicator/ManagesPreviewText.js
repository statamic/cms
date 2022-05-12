export default {

    computed: {
        previewText() {
            const previews = _(this.previews).filter((value, handle) => {
                const config = _.findWhere(this.config.fields, { handle }) || {};
                return config.replicator_preview === undefined ? this.showFieldPreviews : config.replicator_preview;
            });

            return Object.values(previews)
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
    }

}
