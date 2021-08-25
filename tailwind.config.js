const plugin = require('tailwindcss/plugin');

module.exports = {
    mode: 'jit',
    purge: ['./themes/tailwind/**/*.php', './assets/js/tailwind.js'],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                primary: 'var(--va-theme-color)',
                'primary-text': 'var(--va-text-color)',
            },
        },
    },
    variants: {
        extend: {},
    },
    plugins: [
        require('@tailwindcss/forms'),
        plugin(({ addVariant, e }) => {
            addVariant('readonly', ({ modifySelectors, separator }) => {
                modifySelectors(({ className }) => {
                    return `.${e(
                        `readonly${separator}${className}`
                    )}[readonly]`;
                });
            });
        }),
    ],
};
