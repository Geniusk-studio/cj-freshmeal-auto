<?php
/**
 * 로그 확인 페이지
 * URL: /logs
 */

date_default_timezone_set('Asia/Seoul');

// 비밀번호 확인
$password = $_GET['password'] ?? '';
$correctPassword = getenv('ADMIN_PASSWORD') ?: 'test1234';

if ($password !== $correctPassword) {
    die('비밀번호를 입력하세요: ?password=당신의비밀번호');
}

echo "<h1>📋 로그 파일 확인</h1>";
echo "<p>현재 시간: " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

// 로그 파일 목록
$logFiles = [
    'test_send.log' => '테스트 발송 로그',
    'fetch_manual.log' => '수동 실행 로그',
    'stats.json' => '통계 데이터',
    'last_sent.txt' => '마지막 발송 URL'
];

foreach ($logFiles as $file => $description) {
    echo "<h2>$description ($file)</h2>";
    
    $filePath = __DIR__ . '/' . $file;
    
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
        echo htmlspecialchars($content);
        echo "</pre>";
    } else {
        echo "<p style='color: #999;'>파일이 존재하지 않습니다.</p>";
    }
    
    echo "<hr>";
}

// 환경변수 확인
echo "<h2>환경변수 확인</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
echo "RECIPIENTS: " . (getenv('RECIPIENTS') ?: '설정 안 됨') . "\n";
echo "SMTP_USERNAME: " . (getenv('SMTP_USERNAME') ?: '설정 안 됨') . "\n";
echo "ADMIN_PASSWORD: " . (getenv('ADMIN_PASSWORD') ? '설정됨 (****)' : '기본값 (test1234)') . "\n";
echo "</pre>";

echo "<hr>";
echo "<p><a href='/?password=$password'>← 대시보드로 돌아가기</a></p>";
