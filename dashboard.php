<?php
require_once 'config.php';

// 한국 시간대 설정
date_default_timezone_set('Asia/Seoul');

// 통계 파일 경로
define('STATS_FILE', __DIR__ . '/stats.json');

// 통계 데이터 로드
function loadStats() {
    if (!file_exists(STATS_FILE)) {
        return [
            'total_sent' => 0,
            'last_sent_time' => null,
            'last_checked_time' => null
        ];
    }
    return json_decode(file_get_contents(STATS_FILE), true);
}

$stats = loadStats();

// 환경변수에서 수신자 목록 읽기
$recipientsEnv = getenv('RECIPIENTS');
$recipients = $recipientsEnv ? explode(',', $recipientsEnv) : [];
$recipientCount = count($recipients);

// 마지막 발송 URL
$lastSentUrl = file_exists(LAST_SENT_FILE) ? file_get_contents(LAST_SENT_FILE) : '없음';

// 다음 체크 시간 계산 (1시간 후)
$nextCheck = date('Y-m-d H:i', strtotime('+1 hour'));

// 시스템 상태 확인
$isRunning = true; // 이 페이지가 로드되면 실행 중
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CJ 프레시밀 발송 시스템 현황</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Malgun Gothic', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        .status {
            padding: 30px;
        }
        .status-item {
            display: flex;
            align-items: center;
            padding: 20px;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-radius: 12px;
            transition: transform 0.2s;
        }
        .status-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .status-item:last-child {
            margin-bottom: 0;
        }
        .icon {
            font-size: 32px;
            margin-right: 20px;
            width: 50px;
            text-align: center;
        }
        .content {
            flex: 1;
        }
        .label {
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
        }
        .value {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            background: #10b981;
            color: white;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .info-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            margin: 20px 30px;
            border-radius: 8px;
            font-size: 13px;
            color: #856404;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #e9ecef;
        }
        .last-updated {
            margin-top: 10px;
            font-size: 11px;
            color: #999;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .running {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍽️ CJ 프레시밀 발송 시스템 현황</h1>
            <p><?php echo STORE_NAME; ?></p>
        </div>

        <div class="status">
            <div class="status-item">
                <div class="icon">🟢</div>
                <div class="content">
                    <div class="label">시스템 상태</div>
                    <div class="value">
                        <span class="status-badge running">정상 가동 중</span>
                    </div>
                </div>
            </div>

            <div class="status-item">
                <div class="icon">✉️</div>
                <div class="content">
                    <div class="label">메일링 수신자</div>
                    <div class="value"><?php echo $recipientCount; ?>명</div>
                </div>
            </div>

            <div class="status-item">
                <div class="icon">📅</div>
                <div class="content">
                    <div class="label">마지막 발송 시간</div>
                    <div class="value">
                        <?php 
                        if ($stats['last_sent_time']) {
                            echo date('Y-m-d H:i:s', $stats['last_sent_time']);
                        } else {
                            echo '아직 발송 없음';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="status-item">
                <div class="icon">🔄</div>
                <div class="content">
                    <div class="label">다음 체크 예정</div>
                    <div class="value"><?php echo $nextCheck; ?></div>
                </div>
            </div>

            <div class="status-item">
                <div class="icon">📈</div>
                <div class="content">
                    <div class="label">총 발송 횟수</div>
                    <div class="value"><?php echo $stats['total_sent']; ?>회</div>
                </div>
            </div>

            <div class="status-item">
                <div class="icon">🕐</div>
                <div class="content">
                    <div class="label">마지막 체크 시간</div>
                    <div class="value">
                        <?php 
                        if ($stats['last_checked_time']) {
                            echo date('Y-m-d H:i:s', $stats['last_checked_time']);
                        } else {
                            echo '대기 중';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-box">
            ℹ️ 이 시스템은 1시간마다 자동으로 CJ 프레시밀 홈페이지를 확인하며, 새로운 주간 식단표가 등록되면 자동으로 메일을 발송합니다.
            <br><br>
            <strong>⚠️ 정확한 식단표는 <a href="https://front.cjfreshmeal.co.kr/menu/weekMenu" target="_blank" style="color: #667eea; text-decoration: underline;">CJ 프레시밀 공식 홈페이지</a>에서 확인하시기 바랍니다.</strong>
            <br>
            본 서비스는 비공식 서비스이며, 메뉴 정보의 오류, 변경 또는 이로 인한 손해에 대해 법적 책임을 지지 않습니다.
        </div>

        <div class="footer">
            <div>CJ 프레시밀 주간 메뉴표 자동 알림 서비스</div>
            <div>본 서비스는 비공식 서비스이며 CJ프레시웨이와 무관합니다</div>
            <div class="last-updated">
                페이지 갱신: <?php echo date('Y-m-d H:i:s'); ?>
                <br>
                <a href="javascript:location.reload()" style="color: #667eea; text-decoration: none;">🔄 새로고침</a>
            </div>
        </div>
    </div>
</body>
</html>
