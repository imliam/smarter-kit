import {
    defineConfig
} from 'vite';
import path from 'path';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    build: {
        sourcemap: true,
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/dev.css', 'resources/ts/app.ts', 'resources/css/filament/admin/theme.css'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/ts'),
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