#!/bin/bash

# File output
OUTPUT_FILE="public/serviceworker-files.js"

# Awal array
echo "const FILES_TO_CACHE = [" > $OUTPUT_FILE

# Tambahan manual file awal
echo '  "/offline",' >> $OUTPUT_FILE
echo '  "/build/manifest.json",' >> $OUTPUT_FILE

# Fungsi untuk menambahkan semua file js/css dari folder tertentu
add_assets_from() {
    local folder="$1"
    find "$folder" -type f \( -name "*.js" -o -name "*.css" \) | while read file; do
        # Hapus "public" dari path, ubah jadi path relatif dari root
        filepath="/${file#public/}"
        echo "  \"$filepath\"," >> $OUTPUT_FILE
    done
}

# Tambahkan semua asset dari folder berikut
add_assets_from "public/build/assets"
add_assets_from "public/css/filament/filament"
add_assets_from "public/css/filament/forms"
add_assets_from "public/css/filament/support"
add_assets_from "public/css/archilex/filament-toggle-icon-column"
add_assets_from "public/css/asmit/resized-column"
add_assets_from "public/js/filament/filament"
add_assets_from "public/js/filament/notifications"
add_assets_from "public/js/asmit/resized-column"
add_assets_from "public/js/filament/forms/components"
add_assets_from "public/js/filament/support"
add_assets_from "public/js/app/components"
add_assets_from "public/js/filament/tables/components"

# Tutup array
echo "];" >> $OUTPUT_FILE

# Info selesai
echo "✅ Berhasil generate $OUTPUT_FILE"
