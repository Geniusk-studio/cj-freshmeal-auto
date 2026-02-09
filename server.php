<?php
// 요청 URI에 따라 라우팅
$uri = $_SERVER['REQUEST_URI'];

if (strpos($uri, '/trigger.php') !== false || strpos($uri, '/trigger') !== false) {
    // trigger 요청
    require_once 'trigger.php';
} elseif (strpos($uri, '/test_send.php') !== false || strpos($uri, '/test_send') !== false) {
    // 테스트 발송 요청
    require_once 'test_send.php';
} else {
    // 기본 대시보드
    require_once 'dashboard.php';
}
