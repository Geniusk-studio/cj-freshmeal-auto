FROM php:8.2-cli

# 작업 디렉토리 설정
WORKDIR /app

# 시스템 패키지 설치
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip \
    && apt-get clean

# Composer 설치
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 애플리케이션 파일 복사
COPY . /app

# Composer 의존성 설치
RUN composer install --no-dev --optimize-autoloader

# 실행 권한 부여
RUN chmod +x start.sh

# 포트 노출
EXPOSE 8080

# 실행 명령
CMD ["bash", "start.sh"]
