#!/bin/bash
# Render.com 실행 스크립트

# 웹서버를 메인 프로세스로 실행
# cron은 백그라운드에서 실행
php cron.php &
php -S 0.0.0.0:${PORT:-8080} server.php
