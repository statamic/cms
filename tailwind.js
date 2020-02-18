var defaultConfig = require('tailwindcss/defaultConfig')

var colors = {
    'transparent':      'transparent',
    'black':            '#000000',
    'grey-100':         'hsl(210, 20%, 10%)',
    'grey':             'hsl(210, 10%, 50%)', // default grey is grey-70
    'grey-90':          'hsl(210, 15%, 20%)',
    'grey-80':          'hsl(198, 32%, 16%)',
    'grey-70':          'hsl(210, 10%, 50%)',
    'grey-60':          'hsl(210, 15%, 70%)',
    'grey-50':          'hsl(210, 15%, 80%)',
    'grey-40':          'hsl(210, 22%, 89%)',
    'grey-30':          'hsl(210, 30%, 95%)',
    'grey-20':          'hsl(214, 60%, 97.5%)',
    'grey-10':          'hsl(218, 90%, 99%)',
    'white':            '#ffffff',
    'green':            '#479967',
    'green-light':      '#64BD63',
    'blue-lighter':     'hsl(207, 86%, 70%)',
    'blue-light':       'hsl(210, 100%, 75%)',
    'blue':             'hsl(200, 80%, 50%)',
    'blue-dark':        'hsl(200, 75%, 45%)',
    'blue-darker':      'hsl(200, 75%, 40%)',
    'blue-100':         '#eaf5ff',
    'blue-200':         '#abd9ff',
    'blue-300':         '#82c5ff',
    'blue-400':         '#43a9ff',
    'blue-500':         '#2e9fff',
    'blue-600':         '#298fe6',
    'blue-700':         '#257fcc',
    'blue-800':         '#206fb3',
    'blue-900':         '#175080',
    'red-dark':         'hsl(2, 57%, 53%)',
    'red':              'hsl(2, 76%, 60%)',
    'red-light':        'hsl(2, 76%, 70%)',
    'red-lighter':      'hsl(2, 76%, 95%)',
    'yellow':           '#fbfab0',
    'yellow-dark':      '#d8cd1b',
    'orange':           '#f5a82f',
    'pink':             '#ff269e',
    'purple':           '#c471ed',
    'darken-10':        'rgba(0,0,0,.1)',
    'darken-20':        'rgba(0,0,0,.2)',
}

// Set brand colors
colors.primary = colors.blue;

var units = {
    'px':  '1px',
    '0':   '0',
    'sm':  '4px',
    '1':   '8px',
    '1.5': '12px',
    '2':   '16px',
    '3':   '24px',
    '4':   '32px',
    '5':   '40px',
    '6':   '64px',
    '7':   '80px',
    '8':   '120px'
}

module.exports = {

    /*
    |-----------------------------------------------------------------------------
    | Colors                                   https://tailwindcss.com/docs/colors
    |-----------------------------------------------------------------------------
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
            'Inter UI',
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
    | Class name: .text-{size}
    |
    */

    textSizes: {
        '5xs':  '9px',
        '4xs':  '10px',
        '3xs':  '11px',
        '2xs':  '12px',
        'xs':   '13px',
        'sm':   '14px',
        'base': '16px',
        'lg':   '18px',
        'xl':   '21px',
        '2xl':  '26px',
        '3xl':  '36px',
        '4xl':  '48px',
        '5xl':  '60px',
    },


    /*
    |-----------------------------------------------------------------------------
    | Font weights                        https://tailwindcss.com/docs/font-weight
    |-----------------------------------------------------------------------------
    |
    | Class name: .font-{weight}
    |
    */

    fontWeights: {
        'normal':   400,
        'medium':   500,
        'bold':     700,
        'black':    900,
    },


    /*
    |-----------------------------------------------------------------------------
    | Leading (line height)               https://tailwindcss.com/docs/line-height
    |-----------------------------------------------------------------------------
    |
    | Class name: .leading-{size}
    |
    */

    leading: {
        'none':   1,
        'tight':  1.25,
        'normal': 1.5,
        'loose':  1.75,
    },


    /*
    |-----------------------------------------------------------------------------
    | Tracking (letter spacing)        https://tailwindcss.com/docs/letter-spacing
    |-----------------------------------------------------------------------------
    |
    | Class name: .tracking-{size}
    |
    */

    tracking: {
        'tight':  '-0.05em',
        'normal': '-0.004em',
        'wide':   '0.14em',
    },


    /*
    |-----------------------------------------------------------------------------
    | Text colors                          https://tailwindcss.com/docs/text-color
    |-----------------------------------------------------------------------------
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
    | Class name: .bg-{color}
    |
    */

    backgroundColors: colors,


    /*
    |-----------------------------------------------------------------------------
    | Border widths                      https://tailwindcss.com/docs/border-width
    |-----------------------------------------------------------------------------
    |
    | Class name: .border{-side?}{-width?}
    |
    */

    borderWidths: {
        default: '1px',
        '0':     '0',
        '2':     '2px',
        '4':     '4px',
        '6':     '6px',
        '8':     '8px',
    },


    /*
    |-----------------------------------------------------------------------------
    | Border colors                      https://tailwindcss.com/docs/border-color
    |-----------------------------------------------------------------------------
    |
    | Class name: .border-{color}
    |
    */

    borderColors: Object.assign({ default: colors['grey-40'] }, colors),



    /*
    |-----------------------------------------------------------------------------
    | Border radius                     https://tailwindcss.com/docs/border-radius
    |-----------------------------------------------------------------------------
    |
    | Class name: .rounded{-radius?}
    |
    */

    borderRadius: {
        default: '3px',
        'sm':    '2px',
        'md':    '4px',
        'lg':    '8px',
        'full':  '9999px',
        'none':  '0',
    },


    /*
    |-----------------------------------------------------------------------------
    | Width                                     https://tailwindcss.com/docs/width
    |-----------------------------------------------------------------------------
    |
    | Class name: .w-{size}
    |
    */

    width: {
        'auto':   'auto',
        'px':     '1px',
        '1':      '0.25rem',
        '2':      '0.5rem',
        '3':      '0.75rem',
        '4':      '1rem',
        '5':      '1.25rem',
        '6':      '1.5rem',
        '7':      '1.75rem',
        '8':      '2rem',
        '10':     '2.5rem',
        '12':     '3rem',
        '16':     '4rem',
        '24':     '6rem',
        '32':     '8rem',
        '40':     '10rem',
        '48':     '12rem',
        '52':     '13rem',
        '56':     '14rem',
        '60':     '15rem',
        '64':     '16rem',
        '80':     '20rem',
        '96':     '24rem',
        '128':    '32rem',
        '160':    '40rem',
        '240':    '60rem',
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
        '5':      '1.25rem',
        '6':      '24px',
        '7':      '28px',
        '8':      '32px',
        '9':      '36px',
        '10':     '40px',
        '12':     '48px',
        '13':     '52px',
        '14':     '56px',
        '16':     '64px',
        '24':     '80px',
        '48':     '160px',
        '64':     '192px',
        '80':     '220px',
        '96':     '24rem',
        'full':   '100%',
        'screen': '100vh',
    },


    /*
    |-----------------------------------------------------------------------------
    | Minimum width                         https://tailwindcss.com/docs/min-width
    |-----------------------------------------------------------------------------
    |
    | Class name: .min-w-{size}
    |
    */

    minWidth: {
        '0':    '0',
        'xs': '10rem',
        '100': '100px',
        '120': '120px',
        'md':  '160px',
        'lg':  '200px',
        // 'md': '30rem',
        // 'lg': '40rem',
        // 'xl': '50rem',
        // '2xl': '60rem',
        // '3xl': '70rem',
        // '4xl': '80rem',
        // '5xl': '90rem',
        // '5xl': '100rem',
        'full': '100%',
    },


    /*
    |-----------------------------------------------------------------------------
    | Minimum height                       https://tailwindcss.com/docs/min-height
    |-----------------------------------------------------------------------------
    |
    | Class name: .min-h-{size}
    |
    */

    minHeight: {
        '0':      '0',
        '16':     '64px',
        '24':     '80px',
        '48':     '160px',
        'full':   '100%',
        'screen': '100vh'
    },


    /*
    |-----------------------------------------------------------------------------
    | Maximum width                         https://tailwindcss.com/docs/max-width
    |-----------------------------------------------------------------------------
    |
    | Class name: .max-w-{size}
    |
    */

    maxWidth: {
        '3xs':  '5rem',
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
    | Class name: .p{side?}-{size}
    |
    */

    padding: units,


    /*
    |-----------------------------------------------------------------------------
    | Margin                                   https://tailwindcss.com/docs/margin
    |-----------------------------------------------------------------------------
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
    | Class name: .-m{side?}-{size}
    |
    */

    negativeMargin: units,


    /*
    |-----------------------------------------------------------------------------
    | Shadows                                 https://tailwindcss.com/docs/shadows
    |-----------------------------------------------------------------------------
    |
    | Class name: .shadow-{size?}
    |
    */

    shadows: {
        'sm': '1px 2px 4px 0 rgba(0,0,0,.03)',
        default: '0 0 0 0.5px rgba(49,49,93,.03), 0 2px 5px 0 rgba(49,49,93,.1), 0 1px 2px 0 rgba(0,0,0,.08)',
        'lg': '0 7px 14px 0 rgba(50,50,93,.1), 0 2px 8px 0 rgba(0,0,0,.075)',
        'xl': '0 12px 24px 0 rgba(50, 50, 93, .1), 0 10px 20px 0 rgba(0, 0, 0, .075)',
        'popover': '0 0 0 1px rgba(40,45,50,0.05), 0 0 0 1px rgba(40,45,50,0.05), 0 2px 7px 1px rgba(40,45,50,0.16)',
        'set': 'rgba(50,50,93,.1) 0 0 0 1px, rgba(50,50,93,.08) 0 2px 5px, rgba(0,0,0,.07) 0 1px 1.5px,rgba(0,0,0,.08) 0 1px 2px 0, transparent 0 0 0 0',
        'button': '0 1px 0 0 rgba(25,30,35,.05)',
        'inner': 'inset 0px 1px 1px 0px rgba(0,0,0,.05)',
        'none':  'none',
    },



    /*
    |-----------------------------------------------------------------------------
    | Z-index                                 https://tailwindcss.com/docs/z-index
    |-----------------------------------------------------------------------------
    |
    | Class name: .z-{index}
    |
    */

    zIndex: {
        '0':    0,
        '5':    5,
        '10':   10,
        '20':   20,
        '30':   30,
        '40':   40,
        '50':   50,
        '200':  200,
        'top':  1001,
        'max':  9999,
        'auto': 'auto',
    },


    /*
    |-----------------------------------------------------------------------------
    | Opacity                                 https://tailwindcss.com/docs/opacity
    |-----------------------------------------------------------------------------
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
        backgroundColors:     ['responsive', 'hover', 'focus'],
        backgroundPosition:   ['responsive'],
        backgroundRepeat:     ['responsive'],
        backgroundSize:       ['responsive'],
        borderColors:         ['responsive', 'hover', 'focus'],
        borderRadius:         ['responsive'],
        borderStyle:          ['responsive'],
        borderWidths:         ['responsive', 'hover'],
        cursor:               ['responsive'],
        display:              ['responsive'],
        flexbox:              ['responsive'],
        float:                ['responsive'],
        fonts:                [],
        fontWeights:          ['responsive', 'hover'],
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
        outline:              ['focus'],
        overflow:             ['responsive'],
        padding:              ['responsive'],
        pointerEvents:        ['responsive'],
        position:             ['responsive'],
        resize:               ['responsive'],
        shadows:              ['responsive', 'hover', 'focus'],
        svgFill:              [],
        svgStroke:            [],
        textAlign:            ['responsive'],
        textColors:           ['responsive', 'hover', 'group-hover', 'focus'],
        textSizes:            [],
        textStyle:            ['responsive', 'hover', 'focus'],
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
