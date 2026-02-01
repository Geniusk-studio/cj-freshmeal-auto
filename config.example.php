<?php
/**
 * CJ 프레시밀 식단표 자동 발송 시스템
 * 설정 파일 (샘플)
 * 
 * 사용법: 이 파일을 config.php로 복사하고 실제 값으로 수정하세요
 */

// ========== 메일 발송 설정 ==========
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // Gmail 주소
define('SMTP_PASSWORD', 'your-app-password-here'); // Gmail 앱 비밀번호

define('FROM_EMAIL', 'your-email@gmail.com');
define('FROM_NAME', 'CJ 프레시밀 식단표');

// ========== 수신자 목록 (자유롭게 추가/삭제 가능) ==========
$recipients = [
    'recipient1@company.com',
    'recipient2@company.com',
    'recipient3@company.com',
    // 필요한 만큼 추가하세요
];

// ========== API 설정 ==========
define('API_URL', 'https://front.cjfreshmeal.co.kr/meal/v1/weekly-menu?storeIdx=6805');
define('STORE_NAME', '코리아인스트루먼트점 식당');

// ========== 파일 경로 ==========
define('LAST_SENT_FILE', __DIR__ . '/last_sent.txt');
define('TEMP_IMAGE_DIR', __DIR__ . '/temp_images/');

// temp_images 디렉토리가 없으면 생성
if (!file_exists(TEMP_IMAGE_DIR)) {
    mkdir(TEMP_IMAGE_DIR, 0755, true);
}
