<template>
    <div :class="{
        'flex items-center leading-loose': inline
    }">
        <svg xmlns="http://www.w3.org/2000/svg"
            :width="`${size}px`"
            :height="`${size}px`"
            viewBox="0 0 40 40"
            stroke="#737f8c">
            <g fill="none" fill-rule="evenodd">
                <g transform="translate(2 2)" stroke-width="4">
                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"/>
                    <path d="M36 18c0-9.94-8.06-18-18-18">
                        <animateTransform
                            attributeName="transform"
                            type="rotate"
                            from="0 18 18"
                            to="360 18 18"
                            dur="1s"
                            repeatCount="indefinite"/>
                    </path>
                </g>
            </g>
        </svg>

        <div :class="{
            'ml-1 text-sm text-grey': inline,
            'mt-1': !inline
        }">{{ text }}</div>
    </div>
</template>

<script>
export default {

    props: {
        text: {
            type: String,
            default: 'Loading'
        },
        size: {
            type: Number,
            default: 24
        },
        inline: {
            type: Boolean,
            default: false
        }
    }

}
</script>



