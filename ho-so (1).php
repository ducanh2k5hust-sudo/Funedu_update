<?php
// File: ho-so.php (Phiên bản: Chỉnh hồ sơ + Đổi mật khẩu)
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: dangnhap.php"); exit(); }
$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = ""; // Để chỉnh màu thông báo (xanh/đỏ)

// --- XỬ LÝ KHI BẤM LƯU ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_name = $_POST['fullname'];
    $new_age = intval($_POST['age']);
    $new_avatar = $_POST['avatar_url'];
    
    // 1. CẬP NHẬT THÔNG TIN CƠ BẢN
    $stmt = $conn->prepare("UPDATE users SET fullname=?, age=?, current_avatar=? WHERE id=?");
    $stmt->bind_param("sisi", $new_name, $new_age, $new_avatar, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['fullname'] = $new_name;
        $_SESSION['avatar'] = $new_avatar;
        $msg = "✅ Đã lưu thông tin hồ sơ!";
        $msg_type = "green";
        
        // 2. XỬ LÝ ĐỔI MẬT KHẨU (Nếu người dùng có nhập)
        $old_pass = $_POST['old_pass'] ?? '';
        $new_pass = $_POST['new_pass'] ?? '';
        $confirm_pass = $_POST['confirm_pass'] ?? '';

        if (!empty($new_pass)) {
            // Kiểm tra mật khẩu cũ
            $check = $conn->query("SELECT * FROM users WHERE id=$user_id AND password='$old_pass'");
            
            if ($check->num_rows == 0) {
                $msg .= "<br>❌ Nhưng mật khẩu cũ không đúng, chưa đổi được pass nha.";
                $msg_type = "orange"; // Cảnh báo
            } elseif ($new_pass !== $confirm_pass) {
                $msg .= "<br>❌ Mật khẩu mới nhập lại không khớp.";
                $msg_type = "orange";
            } elseif (strlen($new_pass) < 6) {
                $msg .= "<br>⚠️ Mật khẩu mới ngắn quá (phải trên 6 ký tự).";
                $msg_type = "orange";
            } else {
                // Mọi thứ OK -> Cập nhật Pass
                $conn->query("UPDATE users SET password='$new_pass' WHERE id=$user_id");
                $msg .= "<br>🔐 Đã đổi mật khẩu thành công luôn!";
            }
        }
    } else {
        $msg = "❌ Lỗi hệ thống: " . $conn->error;
        $msg_type = "red";
    }
}

// Lấy thông tin user hiện tại để hiển thị ra form
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

// Lấy danh sách Avatar
$sql_avatars = "
    SELECT s.image_url, s.name FROM shop_items s 
    LEFT JOIN user_items u ON s.id = u.item_id 
    WHERE (u.user_id = $user_id) OR (s.price = 0)
    GROUP BY s.id
";
$avatars = $conn->query($sql_avatars);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Hồ Sơ Của Bé</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Comfortaa', cursive; background: #eef1f5; padding: 20px; color: #333; }
        .container { 
            max-width: 700px; margin: 0 auto; background: white; 
            padding: 30px; border-radius: 25px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            border: 4px solid #34495e; 
        }
        
        h1 { text-align: center; color: #e74c3c; margin-top: 0; font-weight: 900; }
        
        .section-title {
            color: #2980b9; font-size: 18px; font-weight: 900;
            border-bottom: 2px dashed #bdc3c7; margin: 25px 0 15px 0; padding-bottom: 5px;
        }

        .form-row { display: flex; gap: 20px; }
        .form-group { margin-bottom: 20px; flex: 1; }
        
        label { display: block; font-weight: 900; margin-bottom: 8px; color: #2c3e50; }
        input { 
            width: 100%; padding: 12px; border: 2px solid #ecf0f1; 
            border-radius: 12px; font-family: inherit; font-size: 15px; 
            box-sizing: border-box; transition: 0.3s; background: #f9f9f9;
        }
        input:focus { border-color: #3498db; background: white; outline: none; }

        /* Grid Avatar */
        .avatar-grid { 
            display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); 
            gap: 15px; margin-top: 10px; 
            max-height: 200px; overflow-y: auto; padding: 5px;
        }
        .avatar-option { 
            cursor: pointer; border: 3px solid transparent; border-radius: 15px; 
            padding: 5px; transition: 0.2s; background: #fff;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }
        .avatar-option:hover { transform: translateY(-3px); }
        .avatar-option.selected { 
            border-color: #e74c3c; background: #fad390; 
            transform: scale(1.05); box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }
        .avatar-option img { width: 100%; border-radius: 10px; }

        /* Nút Lưu */
        .btn-save { 
            background: #e74c3c; color: white; width: 100%; padding: 15px; 
            border: none; border-radius: 15px; font-size: 18px; font-weight: 900; 
            cursor: pointer; box-shadow: 0 6px 0 #c0392b; margin-top: 20px;
            transition: 0.2s;
        }
        .btn-save:hover { background: #ff6b6b; }
        .btn-save:active { transform: translateY(4px); box-shadow: none; }

        /* Box Mật khẩu */
        .password-box {
            background: #fff3cd; padding: 20px; border-radius: 15px;
            border: 2px dashed #f1c40f;
        }
        
        .alert-box {
            padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: bold;
            text-align: center; line-height: 1.6;
        }
    </style>
</head>
<body>

    <div class="container">
        <a href="index.php" style="text-decoration:none; color:#7f8c8d; font-weight:bold; display:inline-flex; align-items:center; gap:5px;">
            <i class="fas fa-arrow-left"></i> Về trang chủ
        </a>
        
        <h1><i class="fas fa-id-card"></i> Hồ Sơ Của Bé</h1>
        
        <?php if($msg): ?>
            <div class="alert-box" style="background: <?php echo ($msg_type == 'green') ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo ($msg_type == 'green') ? '#155724' : '#721c24'; ?>;">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="section-title"><i class="fas fa-user-edit"></i> Thông tin chung</div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Tên hiển thị:</label>
                    <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                </div>
                <div class="form-group" style="max-width: 100px;">
                    <label>Tuổi:</label>
                    <input type="number" name="age" value="<?php echo $user['age']; ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Chọn Avatar đại diện:</label>
                <div class="avatar-grid">
                    <?php while($avt = $avatars->fetch_assoc()): ?>
                        <div class="avatar-option <?php echo ($user['current_avatar'] == $avt['image_url']) ? 'selected' : ''; ?>" 
                             onclick="selectAvatar('<?php echo $avt['image_url']; ?>', this)">
                            <img src="<?php echo $avt['image_url']; ?>">
                        </div>
                    <?php endwhile; ?>
                </div>
                <input type="hidden" name="avatar_url" id="selected_avatar" value="<?php echo $user['current_avatar']; ?>">
            </div>

            <div class="section-title"><i class="fas fa-key"></i> Đổi mật khẩu (Không bắt buộc)</div>
            
            <div class="password-box">
                <div style="font-size: 13px; color: #856404; margin-bottom: 10px;">
                    * Chỉ điền vào đây nếu bé muốn đổi mật khẩu mới thôi nhé!
                </div>
                <div class="form-group">
                    <label>Mật khẩu cũ:</label>
                    <input type="password" name="old_pass" placeholder="Nhập mật khẩu hiện tại...">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Mật khẩu mới:</label>
                        <input type="password" name="new_pass" placeholder="Mật khẩu mới...">
                    </div>
                    <div class="form-group">
                        <label>Nhập lại mật khẩu mới:</label>
                        <input type="password" name="confirm_pass" placeholder="Xác nhận lại...">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-save"><i class="fas fa-save"></i> LƯU TẤT CẢ</button>
        </form>
    </div>

    <script>
        function selectAvatar(url, element) {
            document.getElementById('selected_avatar').value = url;
            document.querySelectorAll('.avatar-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
        }
    </script>
</body>
</html>