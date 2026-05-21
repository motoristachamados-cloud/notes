import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';
import path from 'path';

const isCI = process.env.CI === 'true' || process.env.CI === '1' || process.env.RAILPACK === 'true' || process.env.GITHUB_ACTIONS === 'true';

export default defineConfig({
    resolve: {
        alias: {
            '@': path.resolve(process.cwd(), 'resources/js'),
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        inertia(),
        react({
            babel: {
                plugins: ['babel-plugin-react-compiler'],
            },
        }),
        tailwindcss(),
        // Avoid running Wayfinder's PHP generation during CI/build environments
        // because it executes `php artisan wayfinder:generate` (requires DB).
        ...(!isCI ? [
            wayfinder({
                formVariants: true,
            }),
        ] : []),
    ],
});
