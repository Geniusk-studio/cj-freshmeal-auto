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
    
    // 실제 발송 로직
    try {
        require_once __DIR__ . '/config.php';
        
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;
        require __DIR__ . '/vendor/autoload.php';
        
        // fetch_menu.php 함수들 로드
        $fetchContent = file_get_contents(__DIR__ . '/fetch_menu.php');
        // main() 함수 호출 부분 제거
        $fetchContent = preg_replace('/^main\(\);/m', '', $fetchContent);
        eval('?>' . $fetchContent);
        
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
        if (file_exists($imageFile['path'])) {
            unlink($imageFile['path']);
        }
        
        echo json_encode([
            'success' => true,
            'message' => '테스트 메일이 성공적으로 발송되었습니다!',
            'menu_title' => $menuTitle,
            'recipients' => count($recipients),
            'time' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => '발송 실패: ' . $e->getMessage(),
            'time' => date('Y-m-d H:i:s')
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
