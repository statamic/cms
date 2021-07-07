<script>
export default {
    props: {
        name: String,
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
            return require(`!!html-loader!./../../svg/${this.name}.svg`);
        }
    }
}
</script>
