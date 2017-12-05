module.exports = {
    bind: function() {
        setTimeout(function() {
            autosize(this.el);
        }.bind(this), 10);
    }
}
