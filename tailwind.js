var defaultConfig = require('tailwindcss/defaultConfig')

var colors = {
    'transparent': 'transparent',
    'blue-dark':    '#2d393c',
    'blue':         '#3aa3e3',
    'bg':           '#f1f5f9',
    'bg-light':     '#f8fafc',
    'bg-lighter':   '#fafcff',
    'black':        '#000000',
    'grey-darkest': '#222222',
    'grey-darker':  '#32325d',
    'grey-dark':    '#676767',
    'grey':         '#979797',
    'grey-light':   '#E5E5E5',
    'grey-lighter': '#f6f9fc',
    'green':        '#479967',
    'red':          '#E75650',
    'yellow':       '#fbfab0',
    'yellow-dark':  '#d8cd1b',
    'white':        '#ffffff',
}

var units = {
    'px': '1px',
    '0': '0',
    'sm': '4px',
    '1': '8px',
    '2': '16px',
    '3': '24px',
    '4': '32px',
    '5': '40px',
    '6': '64px',
    '7': '80px',
}

module.exports = {

    /*
    |-----------------------------------------------------------------------------
    | Colors                                   https://tailwindcss.com/docs/colors
    |-----------------------------------------------------------------------------
    |
    | The color palette defined above is also assigned to the "colors" key of
    | your Tailwind config. This makes it easy to access them in your CSS
    | using Tailwind's config helper. For example:
    |
    | .error { color: config('colors.red') }
    |
    */

    colors: colors,


    /*
    |-----------------------------------------------------------------------------
    | Screens                       https://tailwindcss.com/docs/responsive-design
    |-----------------------------------------------------------------------------
    |
    | Screens in Tailwind are translated to CSS media queries. They define the
    | responsive breakpoints for your project. By default Tailwind takes a
    | "mobile first" approach, where each screen size represents a minimum
    | viewport width. Feel free to have as few or as many screens as you
    | want, naming them in whatever way you'd prefer for your project.
    |
    | Tailwind also allows for more complex screen definitions, which can be
    | useful in certain situations. Be sure to see the full responsive
    | documentation for a complete list of options.
    |
    | Class name: .{screen}:{utility}
    |
    */

    screens: {
        'sm': '576px',
        'md': '768px',
        'lg': '992px',
        'xl': '1200px',
    },


    /*
    |-----------------------------------------------------------------------------
    | Fonts                                     https://tailwindcss.com/docs/fonts
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your project's font stack, or font families.
    | Keep in mind that Tailwind doesn't actually load any fonts for you.
    | If you're using custom fonts you'll need to import them prior to
    | defining them here.
    |
    | By default we provide a native font stack that works remarkably well on
    | any device or OS you're using, since it just uses the default fonts
    | provided by the platform.
    |
    | Class name: .font-{name}
    |
    */

    // Fonts
    fonts: {
        'serif': [
            'Georgia',
            'serif'
        ],
        'mono': [
            'Menlo',
            'Monaco',
            'Consolas',
            'Liberation Mono',
            'Courier New',
            'monospace'
        ],
        'sans': [
            '-apple-system',
            'BlinkMacSystemFont',
            'Segoe UI',
            'Roboto',
            'Oxygen',
            'Ubuntu',
            'Cantarell',
            'Fira Sans',
            'Droid Sans',
            'Helvetica Neue',
        ]
    },


    /*
    |-----------------------------------------------------------------------------
    | Text sizes                          https://tailwindcss.com/docs/text-sizing
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your text sizes. Name these in whatever way
    | makes the most sense to you. We use size names by default, but
    | you're welcome to use a numeric scale or even something else
    | entirely.
    |
    | By default Tailwind uses the "rem" unit type for most measurements.
    | This allows you to set a root font size which all other sizes are
    | then based on. That said, you are free to use whatever units you
    | prefer, be it rems, ems, pixels or other.
    |
    | Class name: .text-{size}
    |
    */

    textSizes: {
        '4xs': '10px',
        '3xs': '11px',
        'xxs': '12px',
        'xs': '13px',
        'sm': '14px',
        'base': '16px',
        'lg': '18px',
        'xl': '21px',
        '2xl': '24px',
        '3xl': '32px',
        '4xl': '48px',
        '5xl': '60px',
    },


    /*
    |-----------------------------------------------------------------------------
    | Font weights                        https://tailwindcss.com/docs/font-weight
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your font weights. We've provided a list of
    | common font weight names with their respective numeric scale values
    | to get you started. It's unlikely that your project will require
    | all of these, so we recommend removing those you don't need.
    |
    | Class name: .font-{weight}
    |
    */

    fontWeights: {
        'thin':      200,
        'light':     300,
        'normal':    400,
        'semibold':  500,
        'bold':      700,
    },


    /*
    |-----------------------------------------------------------------------------
    | Leading (line height)               https://tailwindcss.com/docs/line-height
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your line height values, or as we call
    | them in Tailwind, leadings.
    |
    | Class name: .leading-{size}
    |
    */

    leading: {
        'none':   1,
        'tight':  1.25,
        'normal': 1.5,
        'loose':  2,
    },


    /*
    |-----------------------------------------------------------------------------
    | Tracking (letter spacing)        https://tailwindcss.com/docs/letter-spacing
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your letter spacing values, or as we call
    | them in Tailwind, tracking.
    |
    | Class name: .tracking-{size}
    |
    */

    tracking: {
        'tight':  '-0.05em',
        'normal': '-0.03em',
        'wide':   '0.07em',
    },


    /*
    |-----------------------------------------------------------------------------
    | Text colors                          https://tailwindcss.com/docs/text-color
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your text colors. By default these use the
    | color palette we defined above, however you're welcome to set these
    | independently if that makes sense for your project.
    |
    | Class name: .text-{color}
    |
    */

    textColors: colors,


    /*
    |-----------------------------------------------------------------------------
    | Background colors              https://tailwindcss.com/docs/background-color
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your background colors. By default these use
    | the color palette we defined above, however you're welcome to set
    | these independently if that makes sense for your project.
    |
    | Class name: .bg-{color}
    |
    */

    backgroundColors: colors,


    /*
    |-----------------------------------------------------------------------------
    | Border widths                      https://tailwindcss.com/docs/border-width
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your border widths. Take note that border
    | widths require a special "default" value set as well. This is the
    | width that will be used when you do not specify a border width.
    |
    | Class name: .border{-side?}{-width?}
    |
    */

    borderWidths: {
        default: '1px',
        '0':     '0',
        '2':     '2px',
        '4':     '4px',
        '8':     '8px',
    },


    /*
    |-----------------------------------------------------------------------------
    | Border colors                      https://tailwindcss.com/docs/border-color
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your border colors. By default these use the
    | color palette we defined above, however you're welcome to set these
    | independently if that makes sense for your project.
    |
    | Take note that border colors require a special "default" value set
    | as well. This is the color that will be used when you do not
    | specify a border color.
    |
    | Class name: .border-{color}
    |
    */

    borderColors: Object.assign({ default: colors['grey-light'] }, colors),



    /*
    |-----------------------------------------------------------------------------
    | Border radius                     https://tailwindcss.com/docs/border-radius
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your border radius values. If a `default` radius
    | is provided, it will be made available as the non-suffixed `.rounded`
    | utility.
    |
    | Class name: .rounded{-radius?}
    |
    */

    borderRadius: {
        default: '3px',
        'sm':    '2px',
        'lg':    '4px',
        'full':  '9999px',
        'none':  '0',
    },


    /*
    |-----------------------------------------------------------------------------
    | Width                                     https://tailwindcss.com/docs/width
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your width utility sizes. These can be
    | percentage based, pixels, rems, or any other units. By default
    | we provide a sensible rem based numeric scale, a percentage
    | based fraction scale, plus some other common use-cases. You
    | can, of course, modify these values as needed.
    |
    |
    | It's also worth mentioning that Tailwind automatically escapes
    | invalid CSS class name characters, which allows you to have
    | awesome classes like .w-2/3.
    |
    | Class name: .w-{size}
    |
    */

    width: {
        'auto':   'auto',
        'px':     '1px',
        '1':      '2px',
        '2':      '4px',
        '3':      '8px',
        '4':      '16px',
        '6':      '24px',
        '8':      '32px',
        '10':     '40px',
        '12':     '48px',
        '14':     '56px',
        '16':     '64px',
        '24':     '80px',
        '1/2':    '50%',
        '1/3':    '33.33333%',
        '2/3':    '66.66667%',
        '1/4':    '25%',
        '3/4':    '75%',
        '1/5':    '20%',
        '2/5':    '40%',
        '3/5':    '60%',
        '4/5':    '80%',
        '1/6':    '16.66667%',
        '5/6':    '83.33333%',
        'full':   '100%',
        'screen': '100vw',
    },


    /*
    |-----------------------------------------------------------------------------
    | Height                                   https://tailwindcss.com/docs/height
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your height utility sizes. These can be
    | percentage based, pixels, rems, or any other units. By default
    | we provide a sensible rem based numeric scale plus some other
    | common use-cases. You can, of course, modify these values as
    | needed.
    |
    | Class name: .h-{size}
    |
    */

    height: {
        'auto':   'auto',
        'px':     '1px',
        '1':      '2px',
        '2':      '4px',
        '3':      '8px',
        '4':      '16px',
        '6':      '24px',
        '8':      '32px',
        '10':     '40px',
        '12':     '48px',
        '14':     '56px',
        '16':     '64px',
        '24':     '80px',
        'full':   '100%',
        'screen': '100vh',
    },


    /*
    |-----------------------------------------------------------------------------
    | Minimum width                         https://tailwindcss.com/docs/min-width
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your minimum width utility sizes. These can
    | be percentage based, pixels, rems, or any other units. We provide a
    | couple common use-cases by default. You can, of course, modify
    | these values as needed.
    |
    | Class name: .min-w-{size}
    |
    */

    minWidth: {
        '0':    '0',
        'full': '100%',
    },


    /*
    |-----------------------------------------------------------------------------
    | Minimum height                       https://tailwindcss.com/docs/min-height
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your minimum height utility sizes. These can
    | be percentage based, pixels, rems, or any other units. We provide a
    | few common use-cases by default. You can, of course, modify these
    | values as needed.
    |
    | Class name: .min-h-{size}
    |
    */

    minHeight: {
        '0':      '0',
        'full':   '100%',
        'screen': '100vh'
    },


    /*
    |-----------------------------------------------------------------------------
    | Maximum width                         https://tailwindcss.com/docs/max-width
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your maximum width utility sizes. These can
    | be percentage based, pixels, rems, or any other units. By default
    | we provide a sensible rem based scale and a "full width" size,
    | which is basically a reset utility. You can, of course,
    | modify these values as needed.
    |
    | Class name: .max-w-{size}
    |
    */

    maxWidth: {
        '2xs':  '10rem',
        'xs':   '20rem',
        'sm':   '30rem',
        'md':   '40rem',
        'lg':   '50rem',
        'xl':   '60rem',
        '2xl':  '70rem',
        '3xl':  '80rem',
        '4xl':  '90rem',
        '5xl':  '100rem',
        'full': '100%',
    },


    /*
    |-----------------------------------------------------------------------------
    | Maximum height                       https://tailwindcss.com/docs/max-height
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your maximum height utility sizes. These can
    | be percentage based, pixels, rems, or any other units. We provide a
    | couple common use-cases by default. You can, of course, modify
    | these values as needed.
    |
    | Class name: .max-h-{size}
    |
    */

    maxHeight: {
        'full':   '100%',
        'screen': '100vh',
    },


    /*
    |-----------------------------------------------------------------------------
    | Padding                                 https://tailwindcss.com/docs/padding
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your padding utility sizes. These can be
    | percentage based, pixels, rems, or any other units. By default we
    | provide a sensible rem based numeric scale plus a couple other
    | common use-cases like "1px". You can, of course, modify these
    | values as needed.
    |
    | Class name: .p{side?}-{size}
    |
    */

    padding: units,


    /*
    |-----------------------------------------------------------------------------
    | Margin                                   https://tailwindcss.com/docs/margin
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your margin utility sizes. These can be
    | percentage based, pixels, rems, or any other units. By default we
    | provide a sensible rem based numeric scale plus a couple other
    | common use-cases like "1px". You can, of course, modify these
    | values as needed.
    |
    | Class name: .m{side?}-{size}
    |
    */

    margin: Object.assign({ 'auto': 'auto' }, units),


    /*
    |-----------------------------------------------------------------------------
    | Negative margin                 https://tailwindcss.com/docs/negative-margin
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your negative margin utility sizes. These can
    | be percentage based, pixels, rems, or any other units. By default we
    | provide matching values to the padding scale since these utilities
    | generally get used together. You can, of course, modify these
    | values as needed.
    |
    | Class name: .-m{side?}-{size}
    |
    */

    negativeMargin: units,


    /*
    |-----------------------------------------------------------------------------
    | Shadows                                 https://tailwindcss.com/docs/shadows
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your shadow utilities. As you can see from
    | the defaults we provide, it's possible to apply multiple shadows
    | per utility using comma separation.
    |
    | If a `default` shadow is provided, it will be made available as the non-
    | suffixed `.shadow` utility.
    |
    | Class name: .shadow-{size?}
    |
    */

    shadows: {
        'sm': '1px 2px 4px 0 rgba(0,0,0,.03)',
        default: '0 0 0 0.5px rgba(49,49,93,.03), 0 2px 5px 0 rgba(49,49,93,.1), 0 1px 2px 0 rgba(0,0,0,.08)',
        'lg': '0 7px 14px 0 rgba(50,50,93,.1), 0 3px 6px 0 rgba(0,0,0,.07)',
        'inner': 'inset 0 2px 0 0 rgba(0,0,0,0.05)',
        'none':  'none',
    },



    /*
    |-----------------------------------------------------------------------------
    | Z-index                                 https://tailwindcss.com/docs/z-index
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your z-index utility values. By default we
    | provide a sensible numeric scale. You can, of course, modify these
    | values as needed.
    |
    | Class name: .z-{index}
    |
    */

    zIndex: {
        '0':    0,
        '10':   10,
        '20':   20,
        '30':   30,
        '40':   40,
        '50':   50,
        'auto': 'auto',
    },


    /*
    |-----------------------------------------------------------------------------
    | Opacity                                 https://tailwindcss.com/docs/opacity
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your opacity utility values. By default we
    | provide a sensible numeric scale. You can, of course, modify these
    | values as needed.
    |
    | Class name: .opacity-{name}
    |
    */

    opacity: {
        '0':   '0',
        '25':  '.25',
        '50':  '.5',
        '75':  '.75',
        '100': '1',
    },

    /*
    |-----------------------------------------------------------------------------
    | SVG fill                                    https://tailwindcss.com/docs/svg
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your SVG fill colors. By default we just provide
    | `fill-current` which sets the fill to the current text color. This lets you
    | specify a fill color using existing text color utilities and helps keep the
    | generated CSS file size down.
    |
    | Class name: .fill-{name}
    |
    */

    svgFill: {
        'current': 'currentColor',
    },


    /*
    |-----------------------------------------------------------------------------
    | SVG stroke                                  https://tailwindcss.com/docs/svg
    |-----------------------------------------------------------------------------
    |
    | Here is where you define your SVG stroke colors. By default we just provide
    | `stroke-current` which sets the stroke to the current text color. This lets
    | you specify a stroke color using existing text color utilities and helps
    | keep the generated CSS file size down.
    |
    | Class name: .stroke-{name}
    |
    */

    svgStroke: {
        'current': 'currentColor',
    },


    /*
    |-----------------------------------------------------------------------------
    | Modules                   https://tailwindcss.com/docs/configuration#modules
    |-----------------------------------------------------------------------------
    |
    | Here is where you control which modules are generated and what variants are
    | generated for each of those modules.
    |
    | Currently supported variants: 'responsive', 'hover', 'focus'
    |
    | To disable a module completely, use `false` instead of an array.
    |
    */

    modules: {
        appearance:           ['responsive'],
        backgroundAttachment: ['responsive'],
        backgroundColors:     ['responsive', 'hover'],
        backgroundPosition:   ['responsive'],
        backgroundRepeat:     ['responsive'],
        backgroundSize:       ['responsive'],
        borderColors:         ['responsive', 'hover'],
        borderRadius:         ['responsive'],
        borderStyle:          ['responsive'],
        borderWidths:         ['responsive', 'hover'],
        cursor:               ['responsive'],
        display:              ['responsive'],
        flexbox:              ['responsive'],
        float:                ['responsive'],
        fonts:                [],
        fontWeights:          ['hover'],
        height:               ['responsive'],
        leading:              ['responsive'],
        lists:                ['responsive'],
        margin:               ['responsive'],
        maxHeight:            ['responsive'],
        maxWidth:             ['responsive'],
        minHeight:            ['responsive'],
        minWidth:             ['responsive'],
        negativeMargin:       ['responsive'],
        opacity:              ['responsive', 'hover', 'group-hover'],
        overflow:             ['responsive'],
        padding:              ['responsive'],
        pointerEvents:        ['responsive'],
        position:             ['responsive'],
        resize:               ['responsive'],
        shadows:              ['responsive', 'hover'],
        svgFill:              [],
        svgStroke:            [],
        textAlign:            ['responsive'],
        textColors:           ['responsive', 'hover', 'group-hover'],
        textSizes:            [],
        textStyle:            ['hover'],
        tracking:             ['responsive'],
        userSelect:           ['responsive'],
        verticalAlign:        ['responsive'],
        visibility:           ['responsive'],
        whitespace:           ['responsive'],
        width:                ['responsive'],
        zIndex:               ['responsive'],
    },


    /*
    |-----------------------------------------------------------------------------
    | Advanced Options          https://tailwindcss.com/docs/configuration#options
    |-----------------------------------------------------------------------------
    |
    | Here is where you can tweak advanced configuration options. We recommend
    | leaving these options alone unless you absolutely need to change them.
    |
    */

    options: {
        prefix:    '',
        important: true,
        separator: ':',
    },

}
