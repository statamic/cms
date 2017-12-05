require('sweetalert');

module.exports = {

    props: {
        type: { type: String, default: null },
        title: { type: String, default: null },
        timer: { type: String, default: null },
        text: { type: String, default: null },
        button: { type: Boolean, default: true }
    },

    ready: function() {
        swal.setDefaults({ confirmButtonColor: '#748885' });

        // @UX: We might not even need/want the button when auto-timing out.
        // if (this.timer) this.button = false;

        if (this.type === "confirm") {
            swal({
                title: this.title,
                text: this.text,
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes. Do it.",
                closeOnConfirm: false
            });
        } else {
            swal({
                title: this.title,
                text: this.text,
                timer: this.timer,
                type: this.type,
                showConfirmButton: this.button
            });
        }
    }
};