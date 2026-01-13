\<?php
// File: shop-li-xi.php
session_start();
include 'db.php';

// --- QUAN TRỌNG: Ép kiểu chữ sang UTF-8 ngay khi kết nối ---
if (!$conn->set_charset("utf8mb4")) {
    // Nếu server không hỗ trợ utf8mb4 thì dùng utf8 thường
    $conn->set_charset("utf8");
}

if (!isset($_SESSION['user_id'])) {
    header("Location: dangnhap.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Tính tổng điểm kiếm được
$sql_total = "SELECT SUM(score) as total FROM scores WHERE user_id = $user_id";
$res_total = $conn->query($sql_total);
$row_total = $res_total->fetch_assoc();
$total_earned = $row_total['total'] ? $row_total['total'] : 0;

// 2. Tính tổng điểm đã tiêu
$sql_spent = "SELECT SUM(s.price) as spent FROM user_items u JOIN shop_items s ON u.item_id = s.id WHERE u.user_id = $user_id";
$res_spent = $conn->query($sql_spent);
$row_spent = $res_spent->fetch_assoc();
$total_spent = $row_spent['spent'] ? $row_spent['spent'] : 0;

// 3. Số dư hiện tại
$balance = $total_earned - $total_spent;

// 4. Xử lý Mua vật phẩm
if (isset($_POST['buy_item_id'])) {
    $item_id = intval($_POST['buy_item_id']);
    $price = intval($_POST['price']);
    
    if ($balance >= $price) {
        $check = $conn->query("SELECT * FROM user_items WHERE user_id=$user_id AND item_id=$item_id");
        if ($check->num_rows == 0) {
            $conn->query("INSERT INTO user_items (user_id, item_id) VALUES ($user_id, $item_id)");
            echo "<script>alert('Đổi quà thành công! Bé vào trang Hồ Sơ để thay avatar nhé.'); window.location='shop-li-xi.php';</script>";
        } else {
            echo "<script>alert('Bé đã có bạn thú này rồi!');</script>";
        }
    } else {
        echo "<script>alert('Chưa đủ lộc rồi, bé chơi thêm game nhé!');</script>";
    }
}

// 5. Lấy danh sách vật phẩm
$items = $conn->query("SELECT * FROM shop_items");
$my_items = [];
$res_my = $conn->query("SELECT item_id FROM user_items WHERE user_id = $user_id");
while($r = $res_my->fetch_assoc()) $my_items[] = $r['item_id'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Cửa Hàng Thú Cưng</title>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Comfortaa', cursive; 
            background: #fffdf0; 
            padding: 20px; 
            text-align: center; 
        }
        
        .header-shop {
            margin-bottom: 40px;
        }

        .balance-box { 
            background: #d90429; 
            color: #ffc300; 
            padding: 15px 40px; 
            border-radius: 50px; 
            display: inline-flex; 
            align-items: center;
            gap: 15px;
            font-size: 28px; 
            font-weight: 900;
            border: 5px solid #ffc300; 
            box-shadow: 0 8px 0 #9b2226; 
            transform: translateY(0);
            transition: 0.3s;
        }
        
        .balance-box:hover {
            transform: translateY(-5px);
        }

        .grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
            gap: 30px; /* Tăng khoảng cách giữa các thẻ */
            max-width: 1100px; 
            margin: 0 auto; 
            padding-bottom: 50px;
        }

        .item-card { 
            background: white; 
            padding: 25px 15px; 
            border-radius: 25px; 
            border: 4px solid #ffc300; 
            transition: 0.3s; 
            position: relative; 
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            box-shadow: 8px 8px 0 rgba(0,0,0,0.1);
        }

        .item-card:hover { 
            transform:translate(-5px, -5px); 
            box-shadow: 12px 12px 0 #ffc300; 
            border-color: #d90429;
        }

        .item-img { 
            width: 120px; /* Ảnh to hơn */
            height: 120px; 
            object-fit: contain; 
            margin-bottom: 15px; 
            filter: drop-shadow(2px 4px 6px rgba(0,0,0,0.2));
            transition: 0.3s;
        }
        
        .item-card:hover .item-img {
            transform: scale(1.1) rotate(5deg);
        }

        .item-name {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 10px;
            font-weight: 900;
        }

        .price-tag { 
            font-weight: 900; 
            color: #d90429; 
            font-size: 20px; 
            margin-bottom: 15px;
        }

        .btn-buy { 
            background: #27ae60; 
            color: white; 
            border: none; 
            padding: 12px 25px; 
            border-radius: 25px; 
            cursor: pointer; 
            width: 100%; 
            font-family: inherit; 
            font-weight: 900; 
            font-size: 16px;
            border-bottom: 4px solid #1e8449;
            transition: 0.2s;
        }
        
        .btn-buy:active {
            transform: translateY(4px);
            border-bottom: none;
        }

        .btn-owned { 
            background: #95a5a6; 
            border-bottom: 4px solid #7f8c8d;
            cursor: not-allowed; 
        }

        .back-btn { 
            display: inline-block; 
            margin-bottom: 30px; 
            text-decoration: none; 
            color: #333; 
            font-weight: 900; 
            background: white;
            padding: 10px 20px;
            border-radius: 20px;
            border: 3px solid #333;
        }
    </style>
</head>
<body>
    <div style="text-align: left; max-width: 1100px; margin: 0 auto;">
        <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Về nhà</a>
    </div>
    
    <div class="header-shop">
        <div class="balance-box">
            <i class="fas fa-wallet"></i> 
            <span>Ví Lì Xì: <?php echo number_format($balance); ?></span>
        </div>
    </div>

    <div class="grid">
        <?php while($item = $items->fetch_assoc()): ?>
            <div class="item-card">
                <div>
                    <img src="<?php echo $item['image_url']; ?>" class="item-img">
                    <div class="item-name"><?php echo $item['name']; ?></div>
                </div>
                
                <div>
                    <div class="price-tag">
                        <?php echo ($item['price'] == 0) ? "Miễn phí" : number_format($item['price']) . " Lì xì"; ?>
                    </div>
                    
                    <?php if(in_array($item['id'], $my_items) || $item['price'] == 0): ?>
                        <button class="btn-buy btn-owned">Đã sở hữu</button>
                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="buy_item_id" value="<?php echo $item['id']; ?>">
                            <input type="hidden" name="price" value="<?php echo $item['price']; ?>">
                            <button type="submit" class="btn-buy">Đổi Ngay</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>