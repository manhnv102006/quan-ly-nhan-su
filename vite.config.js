import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'node:fs';
import path from 'node:path';

function normalizeLaravelManifestKeys() {
    return {
        name: 'normalize-laravel-manifest-keys',
        closeBundle() {
            const manifestPath = path.resolve('public/build/manifest.json');

            if (!fs.existsSync(manifestPath)) {
                return;
            }

            const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
            const normalizedManifest = {};

            for (const [key, value] of Object.entries(manifest)) {
                const normalizedKey = key
                    .replace(/\\/g, '/')
                    .replace(/^.*?(resources\/)/, '$1');

                normalizedManifest[normalizedKey] = {
                    ...value,
                    src: typeof value.src === 'string'
                        ? value.src.replace(/\\/g, '/').replace(/^.*?(resources\/)/, '$1')
                        : value.src,
                };
            }

            fs.writeFileSync(manifestPath, `${JSON.stringify(normalizedManifest, null, 2)}\n`);
        },
    };
}

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/face-attendance.js', 'resources/js/face-enrollment.js'],
            refresh: true,
        }),
        normalizeLaravelManifestKeys(),
    ],
});
