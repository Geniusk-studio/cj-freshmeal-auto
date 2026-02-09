<?php
/**
 * 수동 실행 트리거
 * 대시보드에서 "지금 바로 확인" 버튼 클릭 시 실행
 */

// 한국 시간대 설정
date_default_timezone_set('Asia/Seoul');

// CORS 헤더 (대시보드에서 AJAX 호출 가능하도록)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

echo json_encode([
    'status' => 'started',
    'message' => '식단표 확인을 시작합니다...',
    'time' => date('Y-m-d H:i:s')
]);

// 출력 버퍼 비우기
if (ob_get_level()) ob_end_flush();
flush();

// 백그라운드에서 실행
if (function_exists('exec')) {
    exec('php ' . __DIR__ . '/fetch_menu.php > /dev/null 2>&1 &');
} else {
    // exec 사용 불가능하면 직접 실행
    include 'fetch_menu.php';
}
