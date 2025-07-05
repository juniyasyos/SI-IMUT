#!/bin/sh
ENV_FILE=".env"

sed -i 's/^SESSION_DRIVER=.*/SESSION_DRIVER=file/' "$ENV_FILE"
sed -i 's/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=sync/' "$ENV_FILE"
sed -i 's/^CACHE_STORE=.*/CACHE_STORE=file/' "$ENV_FILE"
sed -i 's/^CACHE_PREFIX=.*/CACHE_PREFIX=siimut_cache/' "$ENV_FILE"

# Optional: komentar baris redis
sed -i 's/^REDIS_CLIENT=.*/# &/' "$ENV_FILE"
sed -i 's/^REDIS_HOST=.*/# &/' "$ENV_FILE"
sed -i 's/^REDIS_PASSWORD=.*/# &/' "$ENV_FILE"
sed -i 's/^REDIS_PORT=.*/# &/' "$ENV_FILE"

echo "✅ Environment updated to use file-based storage (no Redis)"
