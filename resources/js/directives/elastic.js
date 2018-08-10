import autosize from 'autosize';

export default {
    bind: function() {
        setTimeout(function() {
            autosize(this.el);
        }.bind(this), 10);
    }
}
