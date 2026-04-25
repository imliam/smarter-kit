import path from 'path';
import tailwindcss from "@tailwindcss/vite";
import laravel from 'laravel-vite-plugin';
import {
    defineConfig
} from 'vite';

const __dirname = import.meta.dirname;

export default defineConfig({
    build: {
        sourcemap: true,
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/filament.css'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    test: {
        environment: 'node',
        globals: true,
        exclude: [
            '**/vendor/**',
            '**/node_modules/**',
            '**/.{idea,git,cache,output,temp}/**',
            '**/{karma,rollup,webpack,vite,vitest,jest,ava,babel,nyc,cypress,tsup,build}.config.*'
        ],
    },
});
