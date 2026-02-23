<?php
/**
 * SMTP 연결 테스트
 * URL: /smtp_test?password=test1234
 */

date_default_timezone_set('Asia/Seoul');

// 비밀번호 확인
$password = $_GET['password'] ?? '';
$correctPassword = getenv('ADMIN_PASSWORD') ?: 'test1234';

if ($password !== $correctPassword) {
    die('비밀번호를 입력하세요: ?password=당신의비밀번호');
}

echo "<h1>🔧 SMTP 연결 테스트</h1>";
echo "<pre>";

// 환경변수 확인
echo "=== 환경변수 확인 ===\n";
echo "SMTP_HOST: " . (getenv('SMTP_HOST') ?: 'smtp.gmail.com') . "\n";
echo "SMTP_PORT: " . (getenv('SMTP_PORT') ?: '587') . "\n";
echo "SMTP_USERNAME: " . getenv('SMTP_USERNAME') . "\n";
echo "SMTP_PASSWORD: " . (getenv('SMTP_PASSWORD') ? '설정됨 (' . strlen(getenv('SMTP_PASSWORD')) . '자)' : '❌ 설정 안 됨') . "\n";
echo "FROM_EMAIL: " . getenv('FROM_EMAIL') . "\n";
echo "RECIPIENTS: " . getenv('RECIPIENTS') . "\n\n";

// PHPMailer 테스트
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '/app/vendor/autoload.php';

try {
    echo "=== PHPMailer 연결 테스트 ===\n";
    
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = getenv('SMTP_USERNAME');
    $mail->Password = getenv('SMTP_PASSWORD');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = getenv('SMTP_PORT') ?: 587;
    $mail->SMTPDebug = 3; // 최대 디버그
    $mail->Debugoutput = 'html';
    
    echo "SMTP 서버 연결 테스트 중...\n\n";
    
    // 연결만 테스트 (실제 발송은 안 함)
    $mail->smtpConnect();
    
    echo "\n✅ SMTP 연결 성공!\n";
    
} catch (Exception $e) {
    echo "\n❌ SMTP 연결 실패!\n";
    echo "에러: " . $e->getMessage() . "\n";
}

echo "</pre>";
