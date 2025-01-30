import { computePosition, offset, flip, autoUpdate } from '@floating-ui/dom';

export default {

    data() {
        return {
            cleanupPositionOptions: null
        }
    },

    methods: {
        positionOptions(dropdownList, component, {width}) {
            dropdownList.style.width = width;

            function updatePosition() {
                computePosition(component.$refs.toggle, dropdownList, {
                    placement: 'bottom',
                    middleware: [
                        offset({mainAxis: 0, crossAxis: -1}),
                        flip(),
                    ]
                }).then(({x, y}) => {
                    // Round to avoid blurry text
                    Object.assign(dropdownList.style, {
                        left: `${Math.round(x)}px`,
                        top: `${Math.round(y)}px`,
                    });
                });
            }

            this.cleanupPositionOptions = autoUpdate(
                component.$refs.toggle,
                dropdownList,
                updatePosition
            );
        }
    },

    beforeUnmount() {
        if (this.cleanupPositionOptions) {
            this.cleanupPositionOptions();
        }
    }
}
