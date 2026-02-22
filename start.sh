#!/bin/bash
set -e

echo "Starting CJ Freshmeal Auto System..."

# cron을 백그라운드에서 실행
echo "Starting cron scheduler..."
nohup php /app/cron.php > /tmp/cron.log 2>&1 &

echo "Starting web server on port ${PORT:-8080}..."

# 웹서버 실행 (메인 프로세스)
exec php -S 0.0.0.0:${PORT:-8080} -t /app /app/server.php
