<?php
/**
 * 1시간마다 자동 실행 스크립트
 */

// 한국 시간대 설정
date_default_timezone_set('Asia/Seoul');

while (true) {
    echo "\n=== [" . date('Y-m-d H:i:s') . "] 자동 실행 시작 ===\n";
    
    try {
        include 'fetch_menu.php';
    } catch (Exception $e) {
        echo "오류 발생: " . $e->getMessage() . "\n";
    }
    
    echo "=== 다음 실행: 1시간 후 ===\n\n";
    
    // 1시간 대기 (3600초)
    sleep(3600);
}
