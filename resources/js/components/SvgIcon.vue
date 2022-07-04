<script>
export default {
    props: {
        name: String,
        default: String,
        inline: {
            type: Boolean,
            default: true
        }
    },
    render(createElement) {
        let svg = (this.name.startsWith('<svg')) ? this.name : this.getInlineIcon();

        const compiledTemplate = Vue.compile(svg);

        return compiledTemplate.render.call(this, createElement);
    },
    methods: {
        getInlineIcon() {
            try {
                return require(`!!html-loader!./../../svg/${this.name}.svg`);
            } catch (error) {
                if (this.default) {
                    return require(`!!html-loader!./../../svg/${this.default}.svg`);
                }

                return '';
            }
        }
    }
}
</script>
