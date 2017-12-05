module.exports = {

    template: require('./input.template.html'),

    props: ['query', 'onUp', 'onDown', 'onHit', 'onReset'],

    methods: {

        up: function() {
            this.onUp();
        },

        down: function() {
            this.onDown();
        },

        hit: function() {
            this.onHit();
        },

        reset: function() {
            this.onReset();
        }
    }

};
