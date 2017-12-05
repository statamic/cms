module.exports = {

    params: ['tipText'],

    update() {
        if (! this.params.tipText) return;

        this.el.setAttribute('title', this.params.tipText);

        tippy(this.el, {
            size: 'small',
            animateFill: false,
            theme: 'light',
            performance: true
        });
    }

}
