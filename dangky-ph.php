<?php
session_start();
include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    
    // --- SỬA Ở ĐÂY: MÃ HÓA MẬT KHẨU ---
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    // ---------------------------------
    
    $child_username = $_POST['child_username'];

    // 1. Tìm xem tài khoản của con có tồn tại không
    // (Thêm điều kiện role != 'phuhuynh' để tránh bố mẹ liên kết nhầm với bố mẹ khác)
    $sql_check = "SELECT id FROM users WHERE username = '$child_username' AND (role IS NULL OR role != 'phuhuynh')";
    $res = $conn->query($sql_check);

    if ($res->num_rows > 0) {
        $child_row = $res->fetch_assoc();
        $child_id = $child_row['id'];

        // Kiểm tra xem tên đăng nhập phụ huynh đã có chưa
        $check_ph = $conn->query("SELECT id FROM users WHERE username = '$username'");
        if ($check_ph->num_rows > 0) {
            $message = "❌ Tên đăng nhập này đã có người dùng rồi!";
        } else {
            // 2. Tạo tài khoản Phụ huynh & Liên kết
            // Dùng Prepared Statement để tránh lỗi ký tự lạ
            $stmt = $conn->prepare("INSERT INTO users (fullname, username, password, role, child_id) VALUES (?, ?, ?, 'phuhuynh', ?)");
            $stmt->bind_param("sssi", $fullname, $username, $password, $child_id);
            
            if ($stmt->execute()) {
                // Đăng ký xong chuyển ngay về trang đăng nhập chính
                echo "<script>alert('Đăng ký thành công! Mời bố/mẹ đăng nhập.'); window.location='dangnhap.php';</script>";
                exit;
            } else {
                $message = "Lỗi hệ thống: " . $conn->error;
            }
        }
    } else {
        $message = "❌ Không tìm thấy tên đăng nhập của bé! (Chú ý: Nhập tên đăng nhập, không phải họ tên)";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Đăng Ký Phụ Huynh</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; background: #2c3e50; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: white; padding: 40px; border-radius: 10px; width: 90%; max-width: 350px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #e67e22; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; transition: 0.2s; }
        button:hover { background: #d35400; }
        .err { color: #c0392b; background: #fadbd8; padding: 10px; border-radius: 5px; margin-bottom: 10px; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="box">
        <h2 style="color:#e67e22; margin-top:0;">ĐĂNG KÝ PHỤ HUYNH</h2>
        <p style="font-size: 13px; color: #7f8c8d;">Tạo tài khoản để quản lý và xem điểm của bé.</p>
        
        <?php if($message) echo "<div class='err'>$message</div>"; ?>
        
        <form method="POST">
            <input type="text" name="fullname" placeholder="Họ tên phụ huynh" required>
            <input type="text" name="username" placeholder="Tên đăng nhập (SĐT/Email)" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            
            <div style="margin: 15px 0; border-top: 1px dashed #ccc; padding-top: 15px;">
                <label style="font-weight:bold; color:#2980b9; display:block; text-align:left; margin-bottom:5px;">Nhập tên đăng nhập của bé:</label>
                <input type="text" name="child_username" placeholder="Ví dụ: bi_nguyen_2018" style="border: 2px solid #2980b9; background: #eaf2f8;" required>
                <div style="font-size: 12px; color: #7f8c8d; text-align: left;">* Hệ thống sẽ tự động liên kết tài khoản.</div>
            </div>
            
            <button type="submit">Đăng Ký & Liên Kết</button>
        </form>
        <p><a href="dangnhap.php" style="color: #555; text-decoration: none;">← Quay lại Đăng nhập</a></p>
    </div>
</body>
</html>