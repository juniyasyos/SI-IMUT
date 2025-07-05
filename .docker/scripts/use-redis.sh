#!/bin/sh

# Path ke file .env (ubah jika perlu)
ENV_FILE=".env"

# Validasi file .env
if [ ! -f "$ENV_FILE" ]; then
  echo "❌ File .env tidak ditemukan di path: $ENV_FILE"
  exit 1
fi

# Set ke Redis
sed -i 's/^SESSION_DRIVER=.*/SESSION_DRIVER=redis/' "$ENV_FILE"
sed -i 's/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=redis/' "$ENV_FILE"
sed -i 's/^CACHE_STORE=.*/CACHE_STORE=redis/' "$ENV_FILE"

# Reset CACHE_PREFIX jika sebelumnya diset
if grep -q "^CACHE_PREFIX=" "$ENV_FILE"; then
  sed -i 's/^CACHE_PREFIX=.*/CACHE_PREFIX=/' "$ENV_FILE"
else
  echo "CACHE_PREFIX=" >> "$ENV_FILE"
fi

# Helper function: tambahkan atau update baris
update_or_add() {
  KEY="$1"
  VALUE="$2"
  if grep -q "^$KEY=" "$ENV_FILE"; then
    sed -i "s/^$KEY=.*/$KEY=$VALUE/" "$ENV_FILE"
  else
    echo "$KEY=$VALUE" >> "$ENV_FILE"
  fi
}

# Update konfigurasi Redis
update_or_add REDIS_CLIENT phpredis
update_or_add REDIS_HOST redis
update_or_add REDIS_PASSWORD null
update_or_add REDIS_PORT 6379

echo "✅ .env dikonfigurasi ulang untuk menggunakan Redis"
