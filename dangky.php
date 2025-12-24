<?php
session_start();
include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $name = $_POST['fullname'];

    // Kiểm tra tên đăng nhập đã có chưa
    $check = $conn->prepare("SELECT * FROM users WHERE username=?");
    $check->bind_param("s", $user);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $message = "Tên đăng nhập này đã có người dùng!";
    } else {
        // Mã hóa mật khẩu
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (username, password, fullname) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $user, $hashed_password, $name);
        
        if ($stmt->execute()) {
            header("Location: dangnhap.php");
            exit();
        } else {
            $message = "Có lỗi xảy ra, thử lại sau nhé!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Five Edu</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: #eef1f5;
            display: flex; justify-content: center; align-items: center;
            height: 100vh; margin: 0;
        }
        .login-box {
            background: white; padding: 40px; width: 100%; max-width: 400px;
            border-radius: 20px; border: 3px solid #2c3e50;
            box-shadow: 6px 6px 0 rgba(0,0,0,0.1); text-align: center;
        }
        h2 { color: #d35400; font-weight: 900; margin-bottom: 20px; text-transform: uppercase; }
        input {
            width: 100%; padding: 12px; margin: 10px 0;
            border: 2px solid #ccc; border-radius: 10px; font-size: 16px; font-family: inherit;
        }
        .btn {
            width: 100%; padding: 12px; margin-top: 10px;
            background: #20bf6b; color: white; border: none;
            border-radius: 10px; font-weight: bold; font-size: 18px;
            cursor: pointer; border: 3px solid #146c43;
        }
        .link { margin-top: 15px; display: block; color: #555; text-decoration: none; font-weight: bold; }
        .error { color: #eb4d4b; font-weight: bold; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Đăng Ký</h2>
        <?php if($message != "") echo "<div class='error'>$message</div>"; ?>
        <form method="POST" action="">
            <input type="text" name="fullname" placeholder="Họ và tên bé" required>
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <button type="submit" class="btn">Tạo tài khoản</button>
        </form>
        <a href="dangnhap.php" class="link">Đã có tài khoản? Đăng nhập</a>
        <a href="index.php" class="link" style="color: #999;">Về trang chủ</a>
    </div>
</body>
</html>