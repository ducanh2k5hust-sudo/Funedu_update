<?php
session_start(); // Khởi động phiên làm việc

// Xóa sạch các biến đã lưu (Tên, ID...)
session_unset(); 

// Hủy hoàn toàn phiên đăng nhập
session_destroy(); 

// Chuyển hướng ngay lập tức về trang chủ
header("Location: index.php"); 
exit();
?>