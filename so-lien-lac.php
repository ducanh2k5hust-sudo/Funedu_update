<?php
session_start();
include 'db.php';

// 1. CHẶN CỬA: Phải đăng nhập mới được xem
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Bé cần đăng nhập trước nha!'); window.location='dangnhap.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];
$message = "";

// 2. XỬ LÝ: CẬP NHẬT EMAIL & GỬI BÁO CÁO
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email_phuhuynh'])) {
        $email = $_POST['email_phuhuynh'];
        // Cập nhật email vào bảng users
        $stmt = $conn->prepare("UPDATE users SET email_phuhuynh = ? WHERE id = ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        
        $message = "✅ Đã lưu email phụ huynh thành công!";
    }
}

// 3. LẤY DỮ LIỆU THỐNG KÊ
// Tổng điểm
$sql_total = "SELECT SUM(score) as total FROM scores WHERE user_id = $user_id";
$total_score = $conn->query($sql_total)->fetch_assoc()['total'] ?? 0;

// Xếp hạng
$rank = "Mầm non"; 
if ($total_score > 1000) $rank = "Thần đồng";
elseif ($total_score > 500) $rank = "Bác học nhí";
elseif ($total_score > 200) $rank = "Ong chăm chỉ";

// Lấy email hiện tại
$sql_u = "SELECT email_phuhuynh FROM users WHERE id = $user_id";
$current_email = $conn->query($sql_u)->fetch_assoc()['email_phuhuynh'] ?? '';

// Thống kê hoạt động gần đây
$sql_history = "SELECT * FROM scores WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
$res_history = $conn->query($sql_history);

// AI Nhận xét (Giả lập logic đơn giản để đỡ tốn tiền API)
$nhan_xet = "";
if ($total_score == 0) $nhan_xet = "Bé chưa chơi trò nào cả. Bố mẹ hãy động viên bé nhé!";
elseif ($total_score < 200) $nhan_xet = "Bé đang làm quen rất tốt. Cần luyện tập thêm về Phép cộng.";
else $nhan_xet = "Bé học rất giỏi! Tư duy toán học và ngôn ngữ phát triển vượt bậc.";

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sổ Liên Lạc - Five Edu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #9b2226 0%, #d90429 100%);
            min-height: 100vh;
            display: flex; justify-content: center; align-items: center;
            padding: 20px; margin: 0;
        }
        .paper {
            background: #fffdf0;
            width: 100%; max-width: 600px;
            padding: 40px; border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 8px solid #ffc300;
            position: relative;
        }
        .paper::before {
            content: '🧧'; position: absolute; top: -20px; right: -20px; font-size: 50px;
            animation: swing 3s infinite ease-in-out;
        }
        @keyframes swing { 0%, 100% { transform: rotate(-10deg); } 50% { transform: rotate(10deg); } }
        
        h1 { color: #d90429; text-align: center; font-family: 'Comfortaa'; margin-top: 0; border-bottom: 2px dashed #ffc300; padding-bottom: 15px; }
        
        .info-box { display: flex; justify-content: space-between; margin-bottom: 20px; font-weight: bold; color: #333; }
        
        .score-big { 
            text-align: center; font-size: 40px; color: #d90429; font-weight: 900; 
            background: #ffeaa7; padding: 10px; border-radius: 15px; border: 2px solid #ffc300;
            margin-bottom: 20px;
        }

        .comment-box {
            background: #e3f2fd; border: 2px solid #2196f3; color: #0d47a1;
            padding: 15px; border-radius: 15px; margin-bottom: 20px;
            position: relative;
        }
        .comment-box::before { content: '🤖'; position: absolute; top: -15px; left: 10px; font-size: 30px; background: #fff; border-radius: 50%; }

        .history-list { list-style: none; padding: 0; }
        .history-item { 
            padding: 10px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; 
            font-size: 14px; color: #555;
        }
        
        .form-group { margin-top: 20px; border-top: 2px dashed #ccc; padding-top: 20px; }
        input[type="email"] {
            width: 100%; padding: 12px; border: 2px solid #ffc300; border-radius: 10px;
            box-sizing: border-box; font-family: inherit; margin-bottom: 10px;
        }
        .btn-save {
            width: 100%; padding: 12px; background: #d90429; color: white; border: none;
            border-radius: 10px; font-weight: bold; cursor: pointer; font-size: 16px;
            transition: 0.2s;
        }
        .btn-save:hover { background: #b71c1c; }
        
        .back-btn { display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none; font-weight: bold; }
        .alert { color: green; text-align: center; font-weight: bold; margin-bottom: 10px; }
    </style>
</head>
<body>

    <div class="paper">
        <h1>SỔ LIÊN LẠC ĐIỆN TỬ</h1>
        
        <?php if($message) echo "<div class='alert'>$message</div>"; ?>

        <div class="info-box">
            <span>🎓 Bé: <?php echo $fullname; ?></span>
            <span>🏆 Danh hiệu: <?php echo $rank; ?></span>
        </div>

        <div class="score-big">
            <?php echo number_format($total_score); ?> <span style="font-size: 20px; color: #333;">điểm</span>
        </div>

        <div class="comment-box">
            <strong>Góc nhìn AI:</strong><br>
            "<?php echo $nhan_xet; ?>"
        </div>

        <h3 style="color: #d90429; margin-bottom: 10px;"><i class="fas fa-history"></i> Hoạt động mới nhất</h3>
        <ul class="history-list">
            <?php if ($res_history->num_rows > 0): ?>
                <?php while($row = $res_history->fetch_assoc()): ?>
                    <li class="history-item">
                        <span><?php echo $row['game_name']; ?></span>
                        <b style="color: #27ae60;">+<?php echo $row['score']; ?></b>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li style="text-align: center;">Chưa có dữ liệu học tập.</li>
            <?php endif; ?>
        </ul>

        <div class="form-group">
            <h3 style="margin-top: 0; color: #333;">📧 Đăng ký nhận báo cáo</h3>
            <p style="font-size: 13px; color: #666;">Nhập email bố/mẹ để hệ thống gửi kết quả học tập của bé định kỳ.</p>
            <form method="POST">
                <input type="email" name="email_phuhuynh" value="<?php echo $current_email; ?>" placeholder="Ví dụ: phuhuynh@gmail.com" required>
                <button type="submit" class="btn-save"><i class="fas fa-paper-plane"></i> Lưu & Gửi báo cáo ngay</button>
            </form>
        </div>

        <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Quay về trang chủ</a>
    </div>

</body>
</html>