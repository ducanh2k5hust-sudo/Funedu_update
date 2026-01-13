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
            // Chuyển hướng hoặc thông báo thành công
            echo "<script>alert('Đăng ký thành công! Đăng nhập ngay nhé.'); window.location='dangnhap.php';</script>";
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
    <title>Đăng ký - Five Edu Tết</title>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- BỘ MÀU TẾT --- */
        :root { --tet-red: #d90429; --tet-gold: #ffc300; --tet-cream: #fffdf0; }

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #9b2226 0%, #d90429 100%); /* Nền đỏ chuyển màu */
            display: flex; justify-content: center; align-items: center;
            height: 100vh; margin: 0; overflow: hidden; position: relative;
        }

        /* Hiệu ứng mưa rơi (Lì xì, Hoa) */
        #falling-container { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 1; }
        .falling-item { position: absolute; top: -50px; animation: fall linear infinite; opacity: 0.8; }
        @keyframes fall { 0% { transform: translateY(0) rotate(0deg); opacity: 1; } 100% { transform: translateY(110vh) rotate(360deg); opacity: 0; } }

        /* Khung đăng ký */
        .login-box {
            background: var(--tet-cream); 
            padding: 40px 30px; 
            width: 100%; max-width: 400px;
            border-radius: 30px; 
            border: 5px solid var(--tet-gold); /* Viền vàng */
            box-shadow: 0 10px 30px rgba(0,0,0,0.3); 
            text-align: center;
            position: relative; z-index: 10;
            animation: popIn 0.5s;
        }
        @keyframes popIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        /* Trang trí trên đầu khung */
        .deco-top { position: absolute; top: -35px; left: 50%; transform: translateX(-50%); font-size: 60px; filter: drop-shadow(0 5px 5px rgba(0,0,0,0.2)); }

        h2 { 
            font-family: 'Comfortaa', cursive; 
            color: var(--tet-red); 
            font-weight: 900; margin-bottom: 25px; 
            text-transform: uppercase; font-size: 26px;
        }

        /* Ô nhập liệu có Icon */
        .input-group { position: relative; margin-bottom: 15px; text-align: left; }
        .input-group i { position: absolute; top: 50%; left: 15px; transform: translateY(-50%); color: var(--tet-red); font-size: 18px; }
        
        input {
            width: 100%; padding: 15px 15px 15px 45px; /* Padding trái lớn để né icon */
            border: 2px solid #ddd; border-radius: 15px; 
            font-size: 16px; font-family: inherit; box-sizing: border-box;
            transition: 0.3s;
        }
        input:focus { border-color: var(--tet-gold); outline: none; box-shadow: 0 0 0 3px rgba(255, 195, 0, 0.3); }

        /* Nút bấm */
        .btn {
            width: 100%; padding: 15px; margin-top: 10px;
            background: var(--tet-gold); color: var(--tet-red);
            border: 2px solid var(--tet-red);
            border-radius: 15px; font-weight: 900; font-size: 18px;
            cursor: pointer; text-transform: uppercase;
            transition: 0.3s;
        }
        .btn:hover { background: #ffca2c; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }

        .link { margin-top: 20px; display: block; color: #555; text-decoration: none; font-weight: bold; font-size: 14px; }
        .link:hover { color: var(--tet-red); text-decoration: underline; }
        
        .error { 
            background: #ffebee; color: #c62828; 
            padding: 10px; border-radius: 10px; margin-bottom: 20px; 
            font-weight: bold; font-size: 14px; border: 1px solid #ffcdd2; 
        }
    </style>
</head>
<body>
    <div id="falling-container"></div>

    <div class="login-box">
        <div class="deco-top">🌸</div> <h2>Đăng Ký Tài Khoản</h2>
        
        <?php if($message != "") echo "<div class='error'><i class='fas fa-exclamation-circle'></i> $message</div>"; ?>
        
        <form method="POST" action="">
            <div class="input-group">
                <i class="fas fa-id-card"></i>
                <input type="text" name="fullname" placeholder="Họ và tên bé" required>
            </div>
            
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Tên đăng nhập" required>
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Mật khẩu bí mật" required>
            </div>

            <button type="submit" class="btn">Tạo tài khoản ngay</button>
        </form>

        <a href="dangnhap.php" class="link">Đã có tài khoản? <b>Đăng nhập nhận Lì Xì</b></a>
        <a href="index.php" class="link" style="color: #888; font-weight: normal;">Quay về trang chủ</a>
    </div>

    <script>
        // Script tạo mưa rơi (Hoa, Tiền, Lì xì)
        const container = document.getElementById('falling-container');
        const icons = ['🌸', '🧧', '💰', '✨'];
        for (let i = 0; i < 20; i++) {
            const item = document.createElement('div');
            item.classList.add('falling-item');
            item.innerText = icons[Math.floor(Math.random() * icons.length)];
            item.style.left = Math.random() * 100 + 'vw';
            item.style.fontSize = (Math.random() * 20 + 20) + 'px'; // Kích thước ngẫu nhiên
            item.style.animationDuration = (Math.random() * 3 + 4) + 's'; // Tốc độ rơi ngẫu nhiên
            item.style.animationDelay = (Math.random() * 5) + 's';
            container.appendChild(item);
        }
    </script>
</body>
</html>