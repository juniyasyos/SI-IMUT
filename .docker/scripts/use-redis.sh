#!/bin/sh
ENV_FILE=".env"

sed -i 's/^SESSION_DRIVER=.*/SESSION_DRIVER=redis/' "$ENV_FILE"
sed -i 's/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=redis/' "$ENV_FILE"
sed -i 's/^CACHE_STORE=.*/CACHE_STORE=redis/' "$ENV_FILE"
sed -i 's/^CACHE_PREFIX=.*/CACHE_PREFIX=/' "$ENV_FILE"

# Tambah atau update config redis
grep -q '^REDIS_CLIENT=' "$ENV_FILE" && \
  sed -i 's/^REDIS_CLIENT=.*/REDIS_CLIENT=phpredis/' "$ENV_FILE" || \
  echo "REDIS_CLIENT=phpredis" >> "$ENV_FILE"

grep -q '^REDIS_HOST=' "$ENV_FILE" && \
  sed -i 's/^REDIS_HOST=.*/REDIS_HOST=redis/' "$ENV_FILE" || \
  echo "REDIS_HOST=redis" >> "$ENV_FILE"

grep -q '^REDIS_PASSWORD=' "$ENV_FILE" && \
  sed -i 's/^REDIS_PASSWORD=.*/REDIS_PASSWORD=null/' "$ENV_FILE" || \
  echo "REDIS_PASSWORD=null" >> "$ENV_FILE"

grep -q '^REDIS_PORT=' "$ENV_FILE" && \
  sed -i 's/^REDIS_PORT=.*/REDIS_PORT=6379/' "$ENV_FILE" || \
  echo "REDIS_PORT=6379" >> "$ENV_FILE"

echo "✅ Environment updated to use Redis"
