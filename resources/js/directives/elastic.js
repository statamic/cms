import autosize from 'autosize';

export default {
    inserted: function(el) {
        setTimeout(() => {
            autosize(el);
        });
    }
}
