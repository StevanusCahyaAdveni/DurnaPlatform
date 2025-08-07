import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite"; // Pastikan ini diimpor

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(), // Plugin Tailwind CSS yang sudah ada
    ],
    server: {
        cors: true, // Konfigurasi CORS yang sudah ada
        host: true, // Ini memungkinkan Vite untuk diakses dari IP jaringanmu
        // Kamu juga bisa spesifikkan port jika perlu, tapi defaultnya 5173
        // port: 5173,
        hmr: {
            // Konfigurasi HMR (Hot Module Replacement) jika ada masalah koneksi
            host: "192.168.18.11", // Atau alamat IP lokal komputermu, misal '192.168.1.100'
            protocol: "ws", // Gunakan 'wss' jika kamu menggunakan HTTPS
        },
    },
    // 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
});
