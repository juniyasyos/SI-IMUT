#!/bin/sh

ENV_FILE=".env"

if [ ! -f "$ENV_FILE" ]; then
  echo "❌ File .env tidak ditemukan di path: $ENV_FILE"
  exit 1
fi

sed -i 's/^SESSION_DRIVER=.*/SESSION_DRIVER=file/' "$ENV_FILE"
sed -i 's/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=sync/' "$ENV_FILE"
sed -i 's/^CACHE_STORE=.*/CACHE_STORE=file/' "$ENV_FILE"

if grep -q "^CACHE_PREFIX=" "$ENV_FILE"; then
  sed -i 's/^CACHE_PREFIX=.*/CACHE_PREFIX=siimut_cache/' "$ENV_FILE"
else
  echo "CACHE_PREFIX=siimut_cache" >> "$ENV_FILE"
fi

sed -i 's/^REDIS_CLIENT=/#REDIS_CLIENT=/' "$ENV_FILE"
sed -i 's/^REDIS_HOST=/#REDIS_HOST=/' "$ENV_FILE"
sed -i 's/^REDIS_PASSWORD=/#REDIS_PASSWORD=/' "$ENV_FILE"
sed -i 's/^REDIS_PORT=/#REDIS_PORT=/' "$ENV_FILE"

echo "✅ .env berhasil diubah: Redis dinonaktifkan, menggunakan file-based storage dan sync queue."
