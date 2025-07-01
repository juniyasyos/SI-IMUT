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
            refresh: [
                "app/Livewire/**",
                "app/Filament/**",
                "app/Providers/**",
                "resources/views/**/*.blade.php",
            ],
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
        minify: "esbuild",
        sourcemap: false,
        manifest: true,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes("node_modules")) return "vendor";
                    if (id.includes("resources/js")) return "app";
                    if (id.includes("resources/css")) return "styles";
                },
                chunkFileNames: "assets/[name]-[hash].js",
                entryFileNames: "assets/[name]-[hash].js",
                assetFileNames: "assets/[name]-[hash].[ext]",
            },
        },
        emptyOutDir: true,
    }
});
