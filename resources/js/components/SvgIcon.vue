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
        const compiledTemplate = Vue.compile(this.getInlineIcon());

        return compiledTemplate.render.call(this, createElement);
    },
    methods: {
        getInlineIcon() {
            return require(`!!html-loader!./../../svg/${this.name}.svg`);

        }
    }
}
</script>
