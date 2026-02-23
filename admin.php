<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 확인 - CJ 프레시밀</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }
        p {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            margin-bottom: 20px;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        button {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
        }
        button:active {
            transform: translateY(0);
        }
        .btn-logs {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-smtp {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .error {
            color: #dc2626;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔒 관리자 확인</h1>
        <p>비밀번호를 입력하여 관리자 기능에 접근하세요</p>
        
        <input 
            type="password" 
            id="password" 
            placeholder="ADMIN_PASSWORD 입력"
            onkeypress="if(event.key==='Enter') openLogs()"
        >
        
        <div class="btn-group">
            <button class="btn-logs" onclick="openLogs()">
                📋 LOG 확인
            </button>
            <button class="btn-smtp" onclick="openSmtpTest()">
                🔧 SMTP TEST
            </button>
        </div>
        
        <div id="error" class="error"></div>
    </div>

    <script>
        function openLogs() {
            const password = document.getElementById('password').value;
            
            if (!password) {
                document.getElementById('error').textContent = '비밀번호를 입력해주세요.';
                return;
            }
            
            window.location.href = '/logs?password=' + encodeURIComponent(password);
        }
        
        function openSmtpTest() {
            const password = document.getElementById('password').value;
            
            if (!password) {
                document.getElementById('error').textContent = '비밀번호를 입력해주세요.';
                return;
            }
            
            window.location.href = '/smtp_test?password=' + encodeURIComponent(password);
        }
    </script>
</body>
</html>
