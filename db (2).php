<?php
$servername = "sql103.infinityfree.com";
$username = "if0_40752677";
$password = "3825216a";
$dbname = "if0_40752677_five_edu";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8");
?>