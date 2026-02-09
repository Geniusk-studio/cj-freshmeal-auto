<?php
// 요청 URI에 따라 라우팅
$uri = $_SERVER['REQUEST_URI'];

if (strpos($uri, '/trigger.php') !== false || strpos($uri, '/trigger') !== false) {
    // trigger 요청
    require_once 'trigger.php';
} else {
    // 기본 대시보드
    require_once 'dashboard.php';
}
