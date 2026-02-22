<?php
/**
 * 안전한 간단 서버
 */

// 에러 표시 (디버깅용)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 한국 시간대 설정
date_default_timezone_set('Asia/Seoul');

// 요청 경로 확인
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// 라우팅
if (strpos($path, 'trigger') !== false) {
    // 트리거 요청
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'started',
        'message' => '식단표 확인을 시작합니다...',
        'time' => date('Y-m-d H:i:s')
    ]);
    exit;
}

if (strpos($path, 'test_send') !== false && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // 테스트 발송
    header('Content-Type: application/json');
    
    $password = $_POST['password'] ?? '';
    $correctPassword = getenv('ADMIN_PASSWORD') ?: 'test1234';
    
    if ($password !== $correctPassword) {
        echo json_encode([
            'success' => false,
            'message' => '비밀번호가 올바르지 않습니다.'
        ]);
        exit;
    }
    
    // 실제 발송 로직은 나중에
    echo json_encode([
        'success' => false,
        'message' => '테스트 발송 기능은 현재 준비 중입니다.'
    ]);
    exit;
}

// 기본: 대시보드 표시
if (file_exists(__DIR__ . '/dashboard.php')) {
    require __DIR__ . '/dashboard.php';
} else {
    echo "Error: dashboard.php not found";
}
