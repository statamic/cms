const colors = require('tailwindcss/colors');

module.exports = {
    darkMode: 'class',
    content: ['./resources/**/*.{html,js,vue,blade.php}', './tests/**/*.{html,vue,blade.php}'],
    safelist: [
        {
            pattern: /m[l|r]-(?:[0-9]|10|11|12|14|16)$/,
            variants: ['md', 'lg', 'xl'],
        },
        {
            pattern: /p[x|y]-(?:[0-9]|10|11|12|14|16)$/,
            variants: ['md', 'lg', 'xl'],
        },
    ],
    theme: {
        colors: {
            transparent: 'transparent',
            black: '#000000',
            white: '#ffffff',
            current: 'currentColor',
            blue: {
                100: '#eaf5ff',
                200: '#abd9ff',
                300: '#82c5ff',
                DEFAULT: '#43a9ff',
                400: '#43a9ff',
                500: '#2e9fff',
                600: '#298fe6',
                700: '#257fcc',
                800: '#206fb3',
                900: '#175080',
            },
            gray: {
                100: '#fafcff',
                200: '#f5f8fc',
                300: '#eef2f6',
                400: '#dde3e9',
                500: '#c4ccd4',
                600: '#a7b3be',
                700: '#73808c',
                750: '#354248',
                775: '#2e393d',
                800: '#1c2e36',
                900: '#19292f',
                950: '#141a1f',
                DEFAULT: '#73808c',
            },
            dark: {
                100: '#dfe1e5',
                150: '#bbbdc0',
                175: '#93979a',
                200: '#5f6163',
                250: '#555759',
                275: '#515356',
                300: '#4e5157',
                350: '#43454a',
                400: '#414245',
                500: '#404143',
                550: '#3b3f41',
                575: '#393b40',
                600: '#2b2d30',
                650: '#242628',
                700: '#212223',
                750: '#22242a',
                800: '#1e1f22',
                900: '#171717',
                950: '#161616',
                975: '#131314',
            },
            'dark-blue': {
                100: '#2870F5',
                125: '#1e68f1',
                150: '#1f67ec',
                175: '#1560ec',
                200: '#2a4270',
            },
            orange: {
                light: '#fcc062',
                DEFAULT: '#f5a82f',
                dark: '#e08a1e',
            },
            pink: {
                light: '#ff5ba7',
                DEFAULT: '#ff269e',
                dark: '#e00095',
            },
            purple: {
                light: '#e0b7ff',
                DEFAULT: '#c471ed',
                dark: '#a855cd',
            },
            yellow: {
                light: '#ffffe0',
                DEFAULT: '#fbfab0',
                dark: '#e8dc1e',
            },
            amber: colors.amber,
            green: colors.green,
            red: colors.red,
            'dark-red': '#900000',
        },
        screens: {
            sm: '576px',
            md: '768px',
            lg: '992px',
            xl: '1200px',
            '2xl': '1440px',
        },
        extend: {
            borderColor: {
                DEFAULT: colors.gray[300],
            },
            borderWidth: {
                0: '0',
                3: '3px',
                6: '6px',
            },
            boxShadow: {
                sm: '1px 2px 4px 0 rgba(0,0,0,.03)',
                md: '0px 2px 6px -1px rgba(0,0,0,.1)',
                DEFAULT:
                    '0 0 0 1px rgba(49,49,93,.05), 0 2px 5px 0 rgba(49,49,93,.075), 0 1px 3px 0 rgba(49,49,93,.15)',
                lg: '0 7px 14px 0 rgba(50,50,93,.1), 0 2px 8px 0 rgba(0,0,0,.075)',
                xl: '0 12px 24px 0 rgba(50, 50, 93, .1), 0 10px 20px 0 rgba(0, 0, 0, .075)',
                popover:
                    '0 0 0 1px rgba(40,45,50,0.05), 0 0 0 1px rgba(40,45,50,0.05), 0 2px 7px 1px rgba(40,45,50,0.16)',
                quick: '0 0 0 1px rgba(40,45,50,0.075), 0 0 0 1px rgba(40,45,50,0.075)',
                set: 'rgba(50,50,93,.15) 0 0 0 1px, rgba(50,50,93,.08) 0 2px 5px, rgba(0,0,0,.07) 0 1px 1.5px,rgba(0,0,0,.08) 0 1px 2px 0, transparent 0 0 0 0',
                button: '0 1px 0 0 rgba(25,30,35,.05)',
                inner: 'inset 0px 1px 1px 0px rgba(0,0,0,.05)',
                'inner-dark': 'inset 0px 2px 1px 0px rgba(0,0,0,.15)',
                dark: '0 20px 35px 0 rgba(22,22,22,.45), 0 10px 23px 0 rgba(23,23,23,.65), 0 0 0 1px rgba(85,87,89,.80)',
                'dark-lg':
                    '0 40px 78px 0 rgba(22,22,22,.45), 0 15px 28px 0 rgba(23,23,23,.85), 0 0 0 1px rgba(85,87,89,.85)',
                'dark-sm': '0 3px 3px 0 rgba(22,22,22,.15), 0 1px 2px 0 rgba(23,23,23,.1)',
                'dark-popover':
                    '0 10px 17px 10px rgb(31 33 36 / 76%), 0 2px 1px 1px rgb(31 33 36 / 76%), 0 0 0 1px rgb(109 118 118 / 41%), 0 0 0 1px rgb(110 125 140 / 61%)',
                'dark-set':
                    'rgba(0,0,0,.25) 0 0 0 1px, rgba(0,0,0,.08) 0 2px 5px, rgba(0,0,0,.17) 0 1px 1.5px,rgba(0,0,0,.18) 0 1px 2px 0, transparent 0 0 0 0',
            },
            fontFamily: {
                serif: ['Georgia', 'serif'],
                mono: ['Menlo', 'Monaco', 'Consolas', 'Liberation Mono', 'Courier New', 'monospace'],
                sans: [
                    'Inter var',
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
                ],
            },
            fontSize: {
                '5xs': '9px',
                '4xs': '10px',
                '3xs': '11px',
                '2xs': '12px',
                xs: '13px',
                sm: '14px',
                md: '15px',
                base: '16px',
                lg: '18px',
                xl: '21px',
                '2xl': '24px',
                '3xl': '32px',
                '4xl': '48px',
                '5xl': '60px',
            },
            letterSpacing: {
                normal: '-0.004em',
                widest: '.15em',
            },
            minHeight: {
                40: '10rem',
            },
            maxHeight: {
                'screen-1/2': '50vh',
            },
            maxWidth: {
                '3xs': '5rem',
                '2xs': '10rem',
                xs: '20rem',
                sm: '30rem',
                md: '40rem',
                lg: '50rem',
                xl: '60rem',
                '2xl': '70rem',
                '3xl': '80rem',
                '4xl': '90rem',
                '5xl': '100rem',
                full: '100%',
            },
            spacing: {
                13: '3.25rem',
                30: '7.5rem',
                55: '13.75rem',
            },
            width: {
                128: '32rem',
                160: '40rem',
                240: '60rem',
            },
            zIndex: {
                1: '1',
                2: '2',
                3: '3',
                5: '5',
                200: '200',
                top: '1001',
                max: '9999',
                auto: 'auto',
            },
        },
    },
    plugins: [require('@tailwindcss/container-queries'), require('@tailwindcss/typography')],
    important: true,
};
