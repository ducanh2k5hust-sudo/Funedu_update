<?php
session_start();
include 'db.php'; // Bắt buộc phải có file này để kết nối database

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
        // Kiểm tra mật khẩu (đã được mã hóa lúc đăng ký)
        if (password_verify($pass, $row['password'])) {
            
            // --- KIỂM TRA QUYỀN TRUY CẬP ---
            // Nếu là phụ huynh mà đăng nhập nhầm ở đây thì chuyển sang góc phụ huynh
            if (isset($row['role']) && $row['role'] == 'phuhuynh') {
                $_SESSION['ph_id'] = $row['id'];
                $_SESSION['ph_name'] = $row['fullname'];
                $_SESSION['child_id_linked'] = $row['child_id'];
                header("Location: goc-phu-huynh.php");
                exit();
            }

            // Nếu là học sinh
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['fullname'] = $row['fullname'];
            header("Location: index.php"); // Chuyển về trang chủ
            exit();
        } else {
            $message = "Mật khẩu chưa đúng, bé kiểm tra lại nha!";
        }
    } else {
        $message = "Tên đăng nhập không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập nhận Lì Xì - Five Edu</title>
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

        /* Hiệu ứng mưa rơi */
        #falling-container { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 1; }
        .falling-item { position: absolute; top: -50px; animation: fall linear infinite; opacity: 0.8; }
        @keyframes fall { 0% { transform: translateY(0) rotate(0deg); opacity: 1; } 100% { transform: translateY(110vh) rotate(360deg); opacity: 0; } }

        /* Khung đăng nhập */
        .login-box {
            background: var(--tet-cream); 
            padding: 40px 30px; 
            width: 100%; max-width: 400px;
            border-radius: 30px; 
            border: 5px solid var(--tet-gold); 
            box-shadow: 0 10px 30px rgba(0,0,0,0.3); 
            text-align: center;
            position: relative; z-index: 10;
            animation: popIn 0.5s;
        }
        @keyframes popIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        /* Trang trí trên đầu khung (Bao lì xì) */
        .deco-top { position: absolute; top: -40px; left: 50%; transform: translateX(-50%); font-size: 60px; filter: drop-shadow(0 5px 5px rgba(0,0,0,0.2)); }

        h2 { 
            font-family: 'Comfortaa', cursive; 
            color: var(--tet-red); 
            font-weight: 900; margin-bottom: 25px; 
            text-transform: uppercase; font-size: 26px;
        }

        /* Ô nhập liệu */
        .input-group { position: relative; margin-bottom: 20px; text-align: left; }
        .input-group i { position: absolute; top: 50%; left: 15px; transform: translateY(-50%); color: var(--tet-red); font-size: 18px; }
        
        input {
            width: 100%; padding: 15px 15px 15px 45px; 
            border: 2px solid #ddd; border-radius: 15px; 
            font-size: 16px; font-family: inherit; box-sizing: border-box;
            transition: 0.3s;
        }
        input:focus { border-color: var(--tet-gold); outline: none; box-shadow: 0 0 0 3px rgba(255, 195, 0, 0.3); }

        /* Nút bấm (Màu đỏ cho nổi bật khác với trang đăng ký) */
        .btn {
            width: 100%; padding: 15px; margin-top: 10px;
            background: var(--tet-red); color: var(--tet-gold);
            border: 2px solid var(--tet-gold);
            border-radius: 15px; font-weight: 900; font-size: 18px;
            cursor: pointer; text-transform: uppercase;
            transition: 0.3s;
        }
        .btn:hover { background: #b91d1d; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }

        .link { margin-top: 20px; display: block; color: #555; text-decoration: none; font-weight: bold; font-size: 14px; }
        .link:hover { color: var(--tet-red); text-decoration: underline; }
        
        .error { 
            background: #ffebee; color: #c62828; 
            padding: 10px; border-radius: 10px; margin-bottom: 20px; 
            font-weight: bold; font-size: 14px; border: 1px solid #ffcdd2; 
        }

        /* --- PHẦN MỚI: PHÂN CÁCH PHỤ HUYNH --- */
        .divider { margin: 20px 0; border-top: 2px dashed #ccc; position: relative; }
        .divider-text { 
            position: absolute; top: -12px; left: 50%; transform: translateX(-50%); 
            background: var(--tet-cream); padding: 0 10px; color: #999; font-size: 12px; font-weight: bold;
        }
        .parent-link { color: #2980b9; font-size: 15px; display: block; margin-top: 5px; text-decoration: none; font-weight: 800; transition:0.2s; }
        .parent-link:hover { transform: scale(1.05); color: #c0392b; }
    </style>
</head>
<body>
    <div id="falling-container"></div>

    <div class="login-box">
        <div class="deco-top">🧧</div> <h2>Đăng Nhập</h2>
        
        <?php if($message != "") echo "<div class='error'><i class='fas fa-exclamation-triangle'></i> $message</div>"; ?>
        
        <form method="POST" action="">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Tên đăng nhập" required>
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Mật khẩu" required>
            </div>

            <button type="submit" class="btn">Mở Quà Ngay <i class="fas fa-arrow-right"></i></button>
        </form>

        <a href="dangky.php" class="link">Bé chưa có tài khoản? <b>Đăng ký ngay</b></a>
        
        <div class="divider">
            <span class="divider-text">Dành cho Phụ Huynh</span>
        </div>
        <a href="dangky-ph.php" class="parent-link"><i class="fas fa-user-plus"></i> Đăng Ký Tài Khoản Phụ Huynh</a>
        <a href="goc-phu-huynh.php" class="parent-link" style="color: #e67e22; margin-top: 10px;"><i class="fas fa-user-shield"></i> Đăng Nhập Góc Phụ Huynh</a>
        <a href="index.php" class="link" style="color: #ccc; font-weight: normal; margin-top: 30px;">Quay về trang chủ</a>
    </div>

    <script>
        // Script tạo mưa rơi
        const container = document.getElementById('falling-container');
        const icons = ['🌸', '🧧', '💰', '✨'];
        for (let i = 0; i < 20; i++) {
            const item = document.createElement('div');
            item.classList.add('falling-item');
            item.innerText = icons[Math.floor(Math.random() * icons.length)];
            item.style.left = Math.random() * 100 + 'vw';
            item.style.fontSize = (Math.random() * 20 + 20) + 'px';
            item.style.animationDuration = (Math.random() * 3 + 4) + 's';
            item.style.animationDelay = (Math.random() * 5) + 's';
            container.appendChild(item);
        }
    </script>
</body>
</html>