import js from '@eslint/js';
import prettier from 'eslint-config-prettier';
import globals from 'globals';

/** @type {import('eslint').Linter.Config[]} */
export default [
    js.configs.recommended,
    {
        files: ['./*.js'],
        languageOptions: {
            globals: {
                ...globals.node,
            },
        },
    },
    {
        files: ['resources/js/**/*.js', 'resources/js/*.js'],
        languageOptions: {
            globals: {
                ...globals.browser,
            },
        },
    },
    {
        ignores: ['vendor', 'node_modules', 'public', 'tailwind.config.js'],
    },
    prettier,
];
