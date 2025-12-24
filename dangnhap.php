<?php
session_start();
include 'db.php'; 
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Kiểm tra tên đăng nhập
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Kiểm tra mật khẩu 
        if (password_verify($pass, $row['password'])) {
            // Đăng nhập thành công -> Lưu thông tin
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['fullname'] = $row['fullname'];
            header("Location: index.php"); // Chuyển về trang chủ
            exit();
        } else {
            $message = "Sai mật khẩu rồi bé ơi!";
        }
    } else {
        $message = "Tên đăng nhập không đúng!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Five Edu</title>
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
        h2 { color: #2980b9; font-weight: 900; margin-bottom: 20px; text-transform: uppercase; }
        input {
            width: 100%; padding: 12px; margin: 10px 0;
            border: 2px solid #ccc; border-radius: 10px; font-size: 16px; font-family: inherit;
        }
        .btn {
            width: 100%; padding: 12px; margin-top: 10px;
            background: #3498db; color: white; border: none;
            border-radius: 10px; font-weight: bold; font-size: 18px;
            cursor: pointer; border: 3px solid #2980b9;
        }
        .link { margin-top: 15px; display: block; color: #555; text-decoration: none; font-weight: bold; }
        .error { color: #eb4d4b; font-weight: bold; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Đăng Nhập</h2>
        <?php if($message != "") echo "<div class='error'>$message</div>"; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <button type="submit" class="btn">Vào học ngay</button>
        </form>
        <a href="dangky.php" class="link">Chưa có tài khoản? Đăng ký</a>
        <a href="index.php" class="link" style="color: #999;">Về trang chủ</a>
    </div>
</body>
</html>
