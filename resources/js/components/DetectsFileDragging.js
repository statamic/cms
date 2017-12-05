export default {

    data() {
        return {
            draggingFile: false
        }
    },


    methods: {

        /**
         * When the dragover event is triggered.
         *
         * This event is triggered when something is dragged onto the specified element.
         * If the thing being dragged is not a file, we want to prevent anything
         * from happening. We're only interested in files.
         */
        dragOver() {
            if (! this.$root.draggingNonFile) {
                this.draggingFile = true;
            }
        },

        /**
         * When the dragging has ended.
         */
        dragStop() {
            this.draggingFile = false;
        }

    }

}
