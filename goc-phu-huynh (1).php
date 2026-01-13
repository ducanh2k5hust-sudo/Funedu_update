<?php
session_start();
include 'db.php';

// 1. KIỂM TRA QUYỀN PHỤ HUYNH
if (!isset($_SESSION['ph_id'])) {
    header("Location: dangnhap.php"); // Nếu chưa đăng nhập thì đá về trang login
    exit();
}

$child_id = $_SESSION['child_id_linked'];

// Lấy thông tin bé
$sql_c = "SELECT fullname FROM users WHERE id = $child_id";
$child_name = $conn->query($sql_c)->fetch_assoc()['fullname'];

// Lấy tổng điểm
$sql_total = "SELECT SUM(score) as total FROM scores WHERE user_id = $child_id";
$total_score = $conn->query($sql_total)->fetch_assoc()['total'] ?? 0;

// Lấy lịch sử 5 dòng
$sql_history = "SELECT * FROM scores WHERE user_id = $child_id ORDER BY created_at DESC LIMIT 5";
$res_history = $conn->query($sql_history);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Góc Phụ Huynh - Five Edu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; background: #f0f2f5; margin: 0; }
        .navbar { background: #2c3e50; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .card { background: white; border-radius: 15px; padding: 25px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .logout-btn { background: #c0392b; color: white; text-decoration: none; padding: 8px 15px; border-radius: 5px; font-weight: bold; font-size: 14px; }
        
        /* Nút chức năng quản lý */
        .tools-grid { display: grid; grid-template-columns: 1fr; gap: 15px; }
        .tool-btn { 
            display: flex; align-items: center; gap: 15px;
            background: white; border: 2px solid #3498db; color: #2c3e50;
            padding: 20px; border-radius: 12px; text-decoration: none;
            font-weight: 800; transition: 0.2s;
        }
        .tool-btn:hover { background: #3498db; color: white; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3); }
        .tool-icon { width: 40px; height: 40px; background: #ecf0f1; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 20px; color: #3498db; }
        
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <div class="navbar">
        <div><i class="fas fa-user-shield"></i> PHỤ HUYNH: <strong><?php echo $_SESSION['ph_name']; ?></strong></div>
        <div>
            <span style="margin-right: 15px; opacity: 0.8;">Đang xem bé: <?php echo $child_name; ?></span>
            <a href="logout.php" class="logout-btn">Đăng xuất</a>
        </div>
    </div>

    <div class="container">
        <div>
            <div class="card" style="background: linear-gradient(135deg, #6c5ce7, #a29bfe); color: white;">
                <div style="font-size: 14px; opacity: 0.9;">TỔNG ĐIỂM TÍCH LŨY</div>
                <div style="font-size: 48px; font-weight: 900;"><?php echo number_format($total_score); ?></div>
                <div style="margin-top: 10px; font-weight: bold;">
                    <i class="fas fa-crown" style="color: #f1c40f;"></i> 
                    <?php 
                        if ($total_score > 1000) echo 'Thần Đồng';
                        elseif ($total_score > 500) echo 'Bác Học Nhí';
                        else echo 'Mầm Non Tương Lai';
                    ?>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px dashed #eee; padding-bottom: 10px;">
                    <i class="fas fa-history"></i> Lịch sử học tập mới nhất
                </h3>
                <?php while($row = $res_history->fetch_assoc()): ?>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee;">
                        <span style="font-weight: bold; color: #555;"><?php echo $row['game_name']; ?></span>
                        <span style="color: #27ae60; font-weight: bold;">+<?php echo $row['score']; ?></span>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div>
            <h3 style="color: #2c3e50; margin-top: 0;">🛠 Công cụ quản lý</h3>
            <div class="tools-grid">
                <a href="nap-cau-hoi.php" class="tool-btn">
                    <div class="tool-icon"><i class="fas fa-edit"></i></div>
                    <div>
                        <div style="font-size: 16px;">Nạp Câu Hỏi</div>
                        <div style="font-size: 12px; font-weight: normal; opacity: 0.8;">Thêm bài tập Toán mới cho bé</div>
                    </div>
                </a>

                <a href="so-lien-lac.php" class="tool-btn">
                    <div class="tool-icon"><i class="fas fa-book-open"></i></div>
                    <div>
                        <div style="font-size: 16px;">Sổ Liên Lạc</div>
                        <div style="font-size: 12px; font-weight: normal; opacity: 0.8;">Xem chi tiết & Gửi báo cáo</div>
                    </div>
                </a>

                 <a href="#" class="tool-btn" style="opacity: 0.6; cursor: not-allowed;">
                    <div class="tool-icon"><i class="fas fa-cog"></i></div>
                    <div>
                        <div style="font-size: 16px;">Cài đặt (Sắp ra mắt)</div>
                        <div style="font-size: 12px; font-weight: normal; opacity: 0.8;">Giới hạn giờ chơi game</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

</body>
</html>