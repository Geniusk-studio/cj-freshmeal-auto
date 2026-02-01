<?php
/**
 * CJ 프레시밀 식단표 자동 발송 시스템
 * 설정 파일 (환경변수 버전)
 */

// ========== 메일 발송 설정 ==========
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USERNAME', getenv('SMTP_USERNAME'));
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD'));

define('FROM_EMAIL', getenv('FROM_EMAIL'));
define('FROM_NAME', 'CJ 프레시밀 식단표');

// ========== 수신자 목록 ==========
$recipientsEnv = getenv('RECIPIENTS');
$recipients = $recipientsEnv ? explode(',', $recipientsEnv) : [];

// ========== API 설정 ==========
$storeIdx = getenv('STORE_IDX') ?: '6805';
define('API_URL', 'https://front.cjfreshmeal.co.kr/meal/v1/weekly-menu?storeIdx=' . $storeIdx);
define('STORE_NAME', '코리아인스트루먼트점 식당');

// ========== 파일 경로 ==========
define('LAST_SENT_FILE', __DIR__ . '/last_sent.txt');
define('TEMP_IMAGE_DIR', __DIR__ . '/temp_images/');

// temp_images 디렉토리가 없으면 생성
if (!file_exists(TEMP_IMAGE_DIR)) {
    mkdir(TEMP_IMAGE_DIR, 0755, true);
}
