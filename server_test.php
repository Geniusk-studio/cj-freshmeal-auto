<?php
// 디버깅용 간단 서버
echo "Server is working!<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current DIR: " . __DIR__ . "<br>";
echo "Files: <br>";
print_r(scandir(__DIR__));

echo "<hr>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";

// 실제 파일 확인
if (file_exists(__DIR__ . '/dashboard.php')) {
    echo "dashboard.php: EXISTS<br>";
    require_once __DIR__ . '/dashboard.php';
} else {
    echo "dashboard.php: NOT FOUND<br>";
}
