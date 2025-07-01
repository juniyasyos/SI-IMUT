#!/bin/bash

# File output
OUTPUT_FILE="public/serviceworker-files.js"

# Awal array
echo "const FILES_TO_CACHE = [" > $OUTPUT_FILE

# Tambahan manual file awal
echo '  "/",' >> $OUTPUT_FILE
echo '  "/offline",' >> $OUTPUT_FILE
echo '  "/build/manifest.json",' >> $OUTPUT_FILE

# Ambil semua .js dan .css di public/build/assets
find public/build/assets -type f \( -name "*.js" -o -name "*.css" \) | while read file; do
    # Hapus "public" dari path, ubah jadi path relatif dari root
    filepath="/${file#public/}"
    echo "  \"$filepath\"," >> $OUTPUT_FILE
done

# Tutup array
echo "];" >> $OUTPUT_FILE

# Info selesai
echo "✅ Berhasil generate $OUTPUT_FILE"
