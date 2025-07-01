import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import compression from "vite-plugin-compression";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/css/filament/admin/theme.css",
            ],
            refresh: ["app/Livewire/**", "app/Filament/**", "app/Providers/**"],
            prefetch: true,
        }),
       // Gzip compression
        compression({
            algorithm: "gzip",
            ext: ".gz",
            deleteOriginFile: false,
        }),

        // Brotli compression
        compression({
            algorithm: "brotliCompress",
            ext: ".br",
            deleteOriginFile: false,
        }),
    ],
    build: {
        minify: true,
        sourcemap: false,
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    if (id.includes("node_modules")) return "vendor";
                    if (id.includes("resources/js")) return "scripts";
                    if (id.includes("resources/css")) return "styles";
                },
            },
        },
    },
});
