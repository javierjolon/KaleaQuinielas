const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors:{
                azul: '#0033A0',
                verde: '#0b461c',
                rojo: '#C00707',
                grisOscuro: '#474A4A',
                grisClaro: '#D1D4D1',

                jfondo: '#0B1211',
                jamarillo: '#FFD700',
                jazul: '#2979FF',
                jverde: '#00FF85'
            }
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
