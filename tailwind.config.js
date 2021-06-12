module.exports = {
    mode: 'jit',
    purge: ['./themes/tailwind/**/*.php'],
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
    plugins: [require('@tailwindcss/forms')],
};
