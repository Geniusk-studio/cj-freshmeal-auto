<?php
/**
 * 메인 서버 파일 - 모든 요청 처리
 */

// 한국 시간대 설정
date_default_timezone_set('Asia/Seoul');

// 요청 URI 파싱
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// 라우팅
if ($path === '/trigger' || $path === '/trigger.php') {
    // 수동 확인 트리거
    handleTrigger();
} elseif ($path === '/test_send' || $path === '/test_send.php') {
    // 테스트 메일 발송
    handleTestSend();
} else {
    // 기본 대시보드
    handleDashboard();
}

// ========== 핸들러 함수들 ==========

function handleTrigger() {
    header('Content-Type: application/json');
    
    echo json_encode([
        'status' => 'started',
        'message' => '식단표 확인을 시작합니다...',
        'time' => date('Y-m-d H:i:s')
    ]);
    
    flush();
    
    // 백그라운드에서 실행
    if (function_exists('exec')) {
        exec('php ' . __DIR__ . '/fetch_menu.php > /dev/null 2>&1 &');
    }
}

function handleTestSend() {
    header('Content-Type: application/json');
    
    // POST 요청만 허용
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'success' => false,
            'message' => 'POST 요청만 허용됩니다.'
        ]);
        return;
    }
    
    // 비밀번호 확인
    $password = $_POST['password'] ?? '';
    $correctPassword = getenv('ADMIN_PASSWORD') ?: 'test1234';
    
    if ($password !== $correctPassword) {
        echo json_encode([
            'success' => false,
            'message' => '비밀번호가 올바르지 않습니다.'
        ]);
        return;
    }
    
    // 강제 발송
    try {
        ob_start();
        
        require_once 'config.php';
        
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;
        require 'vendor/autoload.php';
        
        require_once 'fetch_menu.php';
        
        // API에서 데이터 가져오기
        $menuData = fetchMenuData();
        $imageUrl = $menuData['data']['weeklyMenuUrl'];
        
        // 이미지 다운로드
        $imageFile = downloadImage($imageUrl);
        
        // 메뉴 제목 생성
        $menuTitle = extractMenuTitle($imageUrl);
        
        // 메일 발송
        sendEmail($recipients, $imageFile, $menuTitle);
        
        // 통계 업데이트
        updateStats(true, [
            'title' => $menuTitle,
            'url' => $imageUrl
        ]);
        
        // 임시 파일 삭제
        unlink($imageFile['path']);
        
        ob_end_clean();
        
        echo json_encode([
            'success' => true,
            'message' => '테스트 메일이 성공적으로 발송되었습니다!',
            'menu_title' => $menuTitle,
            'recipients' => count($recipients),
            'time' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        ob_end_clean();
        
        echo json_encode([
            'success' => false,
            'message' => '발송 실패: ' . $e->getMessage(),
            'time' => date('Y-m-d H:i:s')
        ]);
    }
}

function handleDashboard() {
    require_once 'dashboard.php';
}
