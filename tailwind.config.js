/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './resources/js/**/*.{vue,js,ts}',
        './Modules/*/resources/js/**/*.{vue,js,ts}',
        './resources/views/**/*.blade.php',
        './Modules/*/resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                primary:      'var(--color-primary)',
                'primary-text': 'var(--color-primary-text)',
                secondary:    'var(--color-secondary)',
                accent:       'var(--color-accent)',
                'sidebar-bg': 'var(--color-sidebar-bg)',
                'sidebar-text':'var(--color-sidebar-text)',
                surface:      'var(--color-surface)',
                background:   'var(--color-background)',
                'app-text':   'var(--color-text)',
            },
        },
    },
    plugins: [],
}