#!/bin/bash
# Render.com 실행 스크립트

# 웹서버와 cron을 동시에 실행
php -S 0.0.0.0:${PORT:-8080} server.php &
php cron.php
