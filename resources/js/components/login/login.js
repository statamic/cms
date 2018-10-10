module.exports = {

    props: {
        showEmailLogin: {
            default: false
        },
        hasError: {
            default: false
        }
    },

    mounted() {
        if (this.hasError) {
            this.$el.parentElement.parentElement.classList.add('animation-shake');
        }
    }

};
