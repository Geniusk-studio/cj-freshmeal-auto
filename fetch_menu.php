<?php
/**
 * CJ 프레시밀 식단표 자동 발송 시스템
 * 메인 스크립트
 */

require_once 'config.php';

// PHPMailer 라이브러리 로드
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

/**
 * API에서 최신 식단표 정보 가져오기
 */
function fetchMenuData() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CJ-Freshmeal-Auto-Checker/1.0 (Korea Instrument; Contact: geniusk.studio@gmail.com)');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("API 호출 실패: " . $error);
    }
    
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception("API 응답 오류: HTTP " . $httpCode);
    }
    
    $data = json_decode($response, true);
    
    if (!$data || $data['status'] !== 'success') {
        throw new Exception("API 응답 형식 오류");
    }
    
    return $data;
}

/**
 * 이미지 다운로드
 */
function downloadImage($imageUrl) {
    $imageData = file_get_contents($imageUrl);
    
    if ($imageData === false) {
        throw new Exception("이미지 다운로드 실패: " . $imageUrl);
    }
    
    // 파일명 추출 (URL에서)
    $filename = basename(parse_url($imageUrl, PHP_URL_PATH));
    $filepath = TEMP_IMAGE_DIR . $filename;
    
    file_put_contents($filepath, $imageData);
    
    return [
        'path' => $filepath,
        'name' => $filename
    ];
}

/**
 * 마지막 발송 기록 확인
 */
function getLastSentUrl() {
    if (!file_exists(LAST_SENT_FILE)) {
        return null;
    }
    return trim(file_get_contents(LAST_SENT_FILE));
}

/**
 * 마지막 발송 기록 저장
 */
function saveLastSentUrl($url) {
    file_put_contents(LAST_SENT_FILE, $url);
}

/**
 * 메일 발송 (이미지를 본문에 직접 삽입)
 */
function sendEmail($recipients, $imageFile, $menuTitle) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP 설정
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        // 발신자
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        
        // 수신자를 BCC로 추가 (비공개 발송)
        foreach ($recipients as $recipient) {
            $mail->addBCC(trim($recipient));
        }
        
        // 이미지를 인라인으로 첨부 (CID 방식)
        $cid = 'menu_image_' . time();
        $mail->addEmbeddedImage($imageFile['path'], $cid, $imageFile['name']);
        
        // 메일 내용
        $mail->isHTML(true);
        $mail->Subject = "[식단표] " . $menuTitle;
        
        $mail->Body = "
        <html>
        <head>
            <meta charset='UTF-8'>
        </head>
        <body style='font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5;'>
            <div style='max-width: 800px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                <h2 style='color: #333; margin-top: 0;'>" . $menuTitle . "</h2>
                <p style='color: #666; margin-bottom: 20px;'>
                    <strong>점포:</strong> " . STORE_NAME . "<br>
                    <strong>발송일시:</strong> " . date('Y-m-d H:i:s') . "
                </p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <div style='text-align: center;'>
                    <img src='cid:" . $cid . "' style='max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 4px;' alt='주간 메뉴표'>
                </div>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <div style='color: #999; font-size: 11px; line-height: 1.6; padding: 15px; background-color: #f9f9f9; border-radius: 4px;'>
                    <p style='margin: 0 0 8px 0; text-align: center; font-weight: bold;'>CJ 프레시밀 주간 메뉴표 자동 알림 서비스</p>
                    <p style='margin: 0 0 8px 0; font-size: 10px;'>
                        본 메일은 PC환경과, 일부 모바일 디바이스의 제약으로 주간 식단표를 확인하기 어려운 분들을 위한 비공식 메일링입니다.
                    </p>
                    <p style='margin: 0 0 12px 0; font-size: 10px;'>
                        문제 발생 및 메일링 수신을 원치 않으시면 <strong>geniusk.studio@gmail.com</strong> 으로 연락 부탁드립니다.
                    </p>
                    <hr style='border: none; border-top: 1px solid #ddd; margin: 10px 0;'>
                    <p style='margin: 0 0 5px 0; font-size: 10px;'>
                        ※ 본 메일은 CJ 프레시밀 공식 홈페이지에 게시된 공개 정보를 자동으로 수집하여 발송하는 비공식 알림 서비스입니다.
                    </p>
                    <p style='margin: 0 0 5px 0; font-size: 10px;'>
                        ※ 메뉴 내용의 정확성, 변경사항 및 실제 제공 여부는 CJ 프레시밀 측에 직접 확인하시기 바랍니다.
                    </p>
                    <p style='margin: 0; font-size: 10px;'>
                        ※ 본 서비스는 CJ프레시웨이와 무관하며, 발송자는 메뉴 정보의 오류, 변경 또는 이로 인한 손해에 대해 법적 책임을 지지 않습니다.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->AltBody = $menuTitle . "\n\n점포: " . STORE_NAME . "\n발송일시: " . date('Y-m-d H:i:s') . "\n\n※ 이 메일은 HTML을 지원하는 메일 클라이언트에서 확인하세요.";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        throw new Exception("메일 발송 실패: " . $mail->ErrorInfo);
    }
}

/**
 * 날짜에서 메뉴 제목 추출
 */
function extractMenuTitle($imageUrl) {
    // 파일명에서 날짜 추출 시도
    $filename = basename($imageUrl);
    
    // YYYYMMDD 형식 찾기
    if (preg_match('/(\d{8})/', $filename, $matches)) {
        $dateStr = $matches[1];
        $year = substr($dateStr, 0, 4);
        $month = substr($dateStr, 4, 2);
        $day = substr($dateStr, 6, 2);
        
        return "주간메뉴표({$month}/{$day}주차)";
    }
    
    // 날짜를 찾지 못하면 기본 제목
    return "주간메뉴표 (" . date('Y-m-d') . ")";
}

/**
 * 통계 데이터 업데이트
 */
function updateStats($sent = false, $menuInfo = null) {
    $statsFile = __DIR__ . '/stats.json';
    $stats = [
        'total_sent' => 0,
        'last_sent_time' => null,
        'last_checked_time' => time(),
        'last_menu_title' => null,
        'last_menu_url' => null,
        'last_menu_acquired_time' => null
    ];
    
    if (file_exists($statsFile)) {
        $existingStats = json_decode(file_get_contents($statsFile), true);
        if ($existingStats) {
            $stats = array_merge($stats, $existingStats);
        }
    }
    
    $stats['last_checked_time'] = time();
    
    if ($sent && $menuInfo) {
        $stats['total_sent']++;
        $stats['last_sent_time'] = time();
        $stats['last_menu_title'] = $menuInfo['title'];
        $stats['last_menu_url'] = $menuInfo['url'];
        $stats['last_menu_acquired_time'] = time();
    }
    
    file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT));
}

/**
 * 메인 실행
 */
function main() {
    global $recipients;
    
    echo "[" . date('Y-m-d H:i:s') . "] 식단표 확인 시작...\n";
    
    // 체크 시간 기록
    updateStats(false);
    
    try {
        // 1. API에서 데이터 가져오기
        $menuData = fetchMenuData();
        $imageUrl = $menuData['data']['weeklyMenuUrl'];
        
        echo "식단표 URL: " . $imageUrl . "\n";
        
        // 2. 중복 확인
        $lastSentUrl = getLastSentUrl();
        if ($lastSentUrl === $imageUrl) {
            echo "이미 발송한 식단표입니다. 스킵합니다.\n";
            return;
        }
        
        // 3. 이미지 다운로드
        echo "이미지 다운로드 중...\n";
        $imageFile = downloadImage($imageUrl);
        
        // 4. 메뉴 제목 생성
        $menuTitle = extractMenuTitle($imageUrl);
        
        // 5. 메일 발송
        echo "메일 발송 중... (수신자: " . count($recipients) . "명)\n";
        sendEmail($recipients, $imageFile, $menuTitle);
        
        // 6. 발송 기록 저장
        saveLastSentUrl($imageUrl);
        
        // 7. 통계 업데이트 (발송 성공)
        updateStats(true, [
            'title' => $menuTitle,
            'url' => $imageUrl
        ]);
        
        // 8. 임시 파일 삭제
        unlink($imageFile['path']);
        
        echo "✓ 메일 발송 완료!\n";
        echo "제목: " . $menuTitle . "\n";
        echo "수신자: " . implode(', ', $recipients) . "\n";
        
    } catch (Exception $e) {
        echo "✗ 오류 발생: " . $e->getMessage() . "\n";
        // 오류 로그 파일에 기록
        file_put_contents(__DIR__ . '/error.log', 
            "[" . date('Y-m-d H:i:s') . "] " . $e->getMessage() . "\n", 
            FILE_APPEND
        );
    }
    
    echo "\n";
}

// 실행
main();
