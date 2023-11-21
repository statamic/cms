import { computePosition, offset, flip, autoUpdate } from '@floating-ui/dom';

export default {
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

            const cleanup = autoUpdate(
                component.$refs.toggle,
                dropdownList,
                updatePosition
            );

            this.$once('hook:destroyed', cleanup);
        }
    }
}
