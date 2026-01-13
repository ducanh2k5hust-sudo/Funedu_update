<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Bé cần đăng nhập để xem thành tích nha!'); window.location='dangnhap.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];

// 1. Lấy Tổng điểm
$sql_total = "SELECT SUM(score) as total FROM scores WHERE user_id = $user_id";
$res_total = $conn->query($sql_total);
$row_total = $res_total->fetch_assoc();
$total_score = $row_total['total'] ? $row_total['total'] : 0;

// 2. Tính danh hiệu
$rank = "Mầm non"; $icon_rank = "fa-seedling"; $color_rank = "#2ecc71";
if ($total_score > 1000) { $rank = "Thần đồng"; $icon_rank = "fa-crown"; $color_rank = "#f1c40f"; }
elseif ($total_score > 500) { $rank = "Bác học nhí"; $icon_rank = "fa-user-graduate"; $color_rank = "#9b59b6"; }
elseif ($total_score > 200) { $rank = "Ong chăm chỉ"; $icon_rank = "fa-award"; $color_rank = "#e67e22"; }

// 3. Lấy lịch sử (Lấy cả cột game_name)
$sql_history = "SELECT * FROM scores WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 20";
$result_history = $conn->query($sql_history);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Thành Tích - Five Edu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; background: #eef1f5; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 800px; margin: 0 auto; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .back-btn { text-decoration: none; color: #333; font-weight: 800; background: white; padding: 10px 20px; border-radius: 12px; border: 3px solid #2c3e50; display: inline-flex; align-items: center; gap: 10px; }
        .summary-card { background: linear-gradient(135deg, #6c5ce7, #a29bfe); color: white; padding: 30px; border-radius: 20px; text-align: center; margin-bottom: 30px; border: 4px solid #2c3e50; box-shadow: 6px 6px 0 rgba(0,0,0,0.1); position: relative; overflow: hidden; }
        .total-score { font-size: 60px; font-weight: 900; text-shadow: 2px 2px 0 rgba(0,0,0,0.2); margin: 10px 0; }
        .rank-badge { background: white; color: <?php echo $color_rank; ?>; padding: 5px 20px; border-radius: 50px; font-weight: 900; display: inline-block; font-size: 18px; border: 2px solid <?php echo $color_rank; ?>; }
        .history-box { background: white; border-radius: 20px; padding: 20px; border: 3px solid #2c3e50; }
        .history-title { font-size: 20px; font-weight: 900; margin-bottom: 15px; border-bottom: 2px dashed #ccc; padding-bottom: 10px; }
        .history-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; margin-bottom: 10px; background: #f8f9fa; border-radius: 10px; border: 2px solid #dfe6e9; transition: 0.2s; }
        .history-item:hover { transform: translateX(5px); border-color: #3498db; background: #e3f2fd; }
        .h-date { font-size: 14px; color: #777; font-weight: bold; }
        .h-score { font-size: 20px; font-weight: 900; color: #20bf6b; }
        .h-icon { width: 40px; height: 40px; background: #ffeaa7; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: 2px solid #fdcb6e; margin-right: 15px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header-bar">
        <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Về trang chủ</a>
        <div style="font-weight: 900; font-size: 20px;">Hồ sơ: <?php echo $fullname; ?></div>
    </div>

    <div class="summary-card">
        <div style="font-size: 18px; opacity: 0.9;">Tổng điểm tích lũy</div>
        <div class="total-score"><?php echo number_format($total_score); ?></div>
        <div class="rank-badge"><i class="fas <?php echo $icon_rank; ?>"></i> <?php echo $rank; ?></div>
    </div>

    <div class="history-box">
        <div class="history-title"><i class="fas fa-history"></i> Lịch sử học tập</div>
        <?php if($result_history->num_rows > 0): ?>
            <?php while($row = $result_history->fetch_assoc()): ?>
                <div class="history-item">
                    <div style="display:flex; align-items:center;">
                        <div class="h-icon">
                            <?php 
                                if(strpos($row['game_name'], 'Giải cứu') !== false) echo '<i class="fas fa-user-knight" style="color:#e74c3c"></i>';
                                else echo '<i class="fas fa-gamepad" style="color:#2ecc71"></i>';
                            ?>
                        </div>
                        <div>
                            <div style="font-weight:bold; color:#2c3e50;"><?php echo $row['game_name']; ?></div>
                            <div class="h-date"><i class="far fa-clock"></i> <?php echo date("H:i - d/m/Y", strtotime($row['created_at'])); ?></div>
                        </div>
                    </div>
                    <div class="h-score">+<?php echo $row['score']; ?> điểm</div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align:center; padding: 30px; color:#777;">Chưa có lịch sử chơi game nào.</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>