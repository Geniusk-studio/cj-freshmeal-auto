<?php
/**
 * 테스트 메일 발송
 * 비밀번호로 보호됨
 */

// 한국 시간대 설정
date_default_timezone_set('Asia/Seoul');

header('Content-Type: application/json');

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'POST 요청만 허용됩니다.'
    ]);
    exit;
}

// 비밀번호 확인
$password = $_POST['password'] ?? '';
$correctPassword = getenv('ADMIN_PASSWORD') ?: 'test1234'; // 환경변수 또는 기본값

if ($password !== $correctPassword) {
    echo json_encode([
        'success' => false,
        'message' => '비밀번호가 올바르지 않습니다.'
    ]);
    exit;
}

// fetch_menu.php 강제 실행 (중복 체크 무시)
ob_start();

try {
    // config 로드
    require_once 'config.php';
    
    // PHPMailer 로드
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    require 'vendor/autoload.php';
    
    // fetch_menu.php의 함수들 로드
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
    
    // 임시 파일 삭제
    unlink($imageFile['path']);
    
    $output = ob_get_clean();
    
    echo json_encode([
        'success' => true,
        'message' => '테스트 메일이 성공적으로 발송되었습니다!',
        'menu_title' => $menuTitle,
        'recipients' => count($recipients),
        'time' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    $output = ob_get_clean();
    
    echo json_encode([
        'success' => false,
        'message' => '발송 실패: ' . $e->getMessage(),
        'time' => date('Y-m-d H:i:s')
    ]);
}
