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
if (strpos($path, 'logs') !== false) {
    // 로그 확인 페이지
    require __DIR__ . '/logs.php';
    exit;
}

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
    
    // 로그 파일에 기록
    $logFile = __DIR__ . '/test_send.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - 테스트 발송 요청 받음\n", FILE_APPEND);
    
    $password = $_POST['password'] ?? '';
    $correctPassword = getenv('ADMIN_PASSWORD') ?: 'test1234';
    
    file_put_contents($logFile, "비밀번호 확인: " . ($password === $correctPassword ? '성공' : '실패') . "\n", FILE_APPEND);
    
    if ($password !== $correctPassword) {
        echo json_encode([
            'success' => false,
            'message' => '비밀번호가 올바르지 않습니다.'
        ]);
        exit;
    }
    
    // 백그라운드에서 fetch_menu.php 실행
    if (function_exists('exec')) {
        $cmd = 'php ' . __DIR__ . '/fetch_menu.php >> ' . __DIR__ . '/fetch_manual.log 2>&1 &';
        file_put_contents($logFile, "실행 명령: " . $cmd . "\n", FILE_APPEND);
        exec($cmd);
        
        echo json_encode([
            'success' => true,
            'message' => '테스트 메일 발송을 시작했습니다! 잠시 후 메일을 확인해주세요. 로그: test_send.log, fetch_manual.log',
            'time' => date('Y-m-d H:i:s')
        ]);
    } else {
        file_put_contents($logFile, "exec 함수 사용 불가\n", FILE_APPEND);
        echo json_encode([
            'success' => false,
            'message' => 'exec 함수를 사용할 수 없습니다.'
        ]);
    }
    exit;
}

// 기본: 대시보드 표시
if (file_exists(__DIR__ . '/dashboard.php')) {
    require __DIR__ . '/dashboard.php';
} else {
    echo "Error: dashboard.php not found";
}
