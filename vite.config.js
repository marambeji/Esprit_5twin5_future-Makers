import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/assets/css/bootstrap.min.css',
                'resources/assets/css/style.css',
                'resources/assets/css/responsive.css',
                'resources/assets/css/owl.carousel.min.css',
                'resources/assets/css/bootstrap-datepicker.min.css',
                'resources/assets/js/images.js',
                'resources/assets/js/agropro.js',
                'resources/mazer/compiled/css/app.css',
                'resources/mazer/compiled/css/app-dark.css',
                'resources/mazer/nutritrace.css',
                'resources/mazer/mazer.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
